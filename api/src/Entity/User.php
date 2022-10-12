<?php

namespace App\Entity;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\ConfirmSignupController;
use App\Repository\UserRepository;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\UuidV6;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Validator\Constraints as Assert;
use App\Filter\MultipleFieldSearchFilter;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ["email"])]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Get(
            name: "confirm_signup",
            controller: ConfirmSignupController::class,
            uriTemplate: "/users/{id}/confirm"
        ),
        new Put(
            uriTemplate: "/users/{id}/reset",
            security: "is_granted('ROLE_ADMIN') or is_granted('PATCH_USER', object)",
            validationContext: [
                "groups" => ["reset_password"]
            ]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            validationContext: [
                "groups" => ["Default", "registration"]
            ]
        ),
    ]
)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{


    const ADMIN_READ = ["admin:user:collection:get", "admin:user:item:get"];
    const ADMIN_UPDATE = ["admin:user:item:put"];
    const ADMIN_CREATE = ["admin:user:collection:post"];
    const OWNER_READ = ["me", "owner:user:collection:post"];
    const OWNER_UPDATE = ["owner:user:item:put"];
    const REGISTERATION = ["anon:user:collection:post", "anon:user:item:get"];
    
    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateTimestamps(): void
    {
        $now = new \DateTime("now");
        $this->setUpdatedAt($now);
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt($now);
        }
    }

    public function __construct()
    {
        $this->roles = [];
        $this->organisations = new ArrayCollection();
        $this->ownedOrganisation = new ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(["me", ...self::ADMIN_READ,  ...self::OWNER_READ ])]
    private $id;

    #[ORM\Column(type: 'text', unique: true)]
    #[Assert\Email()]
    #[Groups(["me", ...self::ADMIN_READ, ...self::OWNER_UPDATE, ...self::ADMIN_CREATE, ...self::ADMIN_UPDATE, ...self::REGISTERATION, ...self::OWNER_READ ])]
    #[MaxDepth(1)]
    private $email;

    #[ORM\Column(type: 'text', nullable: true)]
    private $password;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["me", ...self::ADMIN_READ, ...self::ADMIN_UPDATE])]
    private $isActive;

    #[ORM\Column(type: "json", options: ["default" => "[\"ROLE_USER\"]"])]
    #[Groups(["me", ...self::ADMIN_READ, ...self::ADMIN_UPDATE])]
    #[ApiProperty(
        jsonldContext: ["@type" => "http://www.w3.org/2001/XMLSchema#array"]
    )]
    private $roles = [];

    /**
     * Will not be persisted to doctrine, this is just for encoding the password
     */
    #[SerializedName("password")]
    #[Groups(["anon:user:collection:post", "admin:user:collection:post", "admin:user:item:put", "anon:user:item:reset_password"])]
    #[Assert\Length(min: 8)]
    #[Assert\NotBlank(groups: ["registration", "reset_password"])]
    private $plainPassword;


    #[Groups([...self::REGISTERATION, "admin:user:collection:post", "admin:user:item:put", "anon:user:item:reset_password"])]
    #[Assert\Length(min: 8)]
    #[Assert\NotBlank(groups: ["registration", "reset_password"])]
    private $repeatPassword;


    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["me", ...self::REGISTERATION, ...self::ADMIN_READ, ...self::ADMIN_UPDATE, ])]
    private $isConfirmed;

    #[ORM\Column(type: 'text', nullable: true)]
    private $confirmationToken;


    #[Groups(["me", ...self::REGISTERATION, ...self::ADMIN_READ, ...self::OWNER_READ])]
    public function getDisplayName()
    {
        return $this->email;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["me", ...self::ADMIN_READ])]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["me", ...self::ADMIN_READ])]
    private $updatedAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $resetPasswordToken;

    #[ORM\Column(type: 'integer', options: ["default" => 0])]
    #[Groups(["me", ...self::ADMIN_READ])]
    private $numberOfLogins = 0;

    #[ORM\ManyToMany(targetEntity: Organisation::class, mappedBy: 'members')]
    private Collection $organisations;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Organisation::class)]
    private Collection $ownedOrganisation;

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function addRole($role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
        $this->repeatPassword = null;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     *
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @return  self
     */
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     *
     */
    public function getRepeatPassword(): ?string
    {
        return $this->repeatPassword;
    }

    /**
     * @return  self
     */
    public function setRepeatPassword(string $repeatPassword): self
    {
        $this->repeatPassword = $repeatPassword;

        return $this;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(?bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getNumberOfLogins(): int
    {
        return $this->numberOfLogins;
    }

    public function setNumberOfLogins(int $numberOfLogins): self
    {
        $this->numberOfLogins = $numberOfLogins;

        return $this;
    }

    /**
     * @return Collection<int, Organisation>
     */
    public function getOrganisations(): Collection
    {
        return $this->organisations;
    }

    public function addOrganisation(Organisation $organisation): self
    {
        if (!$this->organisations->contains($organisation)) {
            $this->organisations->add($organisation);
            $organisation->addMember($this);
        }

        return $this;
    }

    public function removeOrganisation(Organisation $organisation): self
    {
        if ($this->organisations->removeElement($organisation)) {
            $organisation->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Organisation>
     */
    public function getOwnedOrganisation(): Collection
    {
        return $this->ownedOrganisation;
    }

    public function addOwnedOrganisation(Organisation $ownedOrganisation): self
    {
        if (!$this->ownedOrganisation->contains($ownedOrganisation)) {
            $this->ownedOrganisation->add($ownedOrganisation);
            $ownedOrganisation->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedOrganisation(Organisation $ownedOrganisation): self
    {
        if ($this->ownedOrganisation->removeElement($ownedOrganisation)) {
            // set the owning side to null (unless already changed)
            if ($ownedOrganisation->getOwner() === $this) {
                $ownedOrganisation->setOwner(null);
            }
        }

        return $this;
    }


}

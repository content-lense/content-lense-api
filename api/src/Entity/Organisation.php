<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[ApiResource]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organisations')]
    private Collection $members;

    #[ORM\ManyToOne(inversedBy: 'ownedOrganisation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, unique: true)]
    private ?string $apiToken = null;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Article::class)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: AnalysisMicroservice::class, orphanRemoval: true)]
    private Collection $analysisMicroservices;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->analysisMicroservices = new ArrayCollection();
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setOrganisation($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getOrganisation() === $this) {
                $article->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnalysisMicroservice>
     */
    public function getAnalysisMicroservices(): Collection
    {
        return $this->analysisMicroservices;
    }

    public function addAnalysisMicroservice(AnalysisMicroservice $analysisMicroservice): self
    {
        if (!$this->analysisMicroservices->contains($analysisMicroservice)) {
            $this->analysisMicroservices->add($analysisMicroservice);
            $analysisMicroservice->setOrganisation($this);
        }

        return $this;
    }

    public function removeAnalysisMicroservice(AnalysisMicroservice $analysisMicroservice): self
    {
        if ($this->analysisMicroservices->removeElement($analysisMicroservice)) {
            // set the owning side to null (unless already changed)
            if ($analysisMicroservice->getOrganisation() === $this) {
                $analysisMicroservice->setOrganisation(null);
            }
        }

        return $this;
    }
}

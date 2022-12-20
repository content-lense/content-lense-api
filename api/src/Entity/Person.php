<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\UuidV6;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\MultipleFieldSearchFilter;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'firstName' => 'ipartial', 'gender' => 'ipartial', 'age' => 'exact', 'isAuthor' => 'exact', 'isMentionedPerson' => 'exact'])]
#[ApiFilter(MultipleFieldSearchFilter::class, properties: [
    "firstName", "lastName", "gender", "age"
])]
// #[ApiFilter(OrderFilter::class, properties: ["lastName"], arguments: ['orderParameterName' => 'order'])]
#[ApiResource(
    order: ["lastName" => "ASC"]
)]
#[ORM\HasLifecycleCallbacks]
class Person
{

   
    const USER_READ = ["user:person:collection:get", "user:person:item:get"];
    const USER_UPDATE = ["user:person:item:put"];
    const USER_POST = ["user:person:collection:post", "user:person:item:post"];
    const IN_ARTICLE = ["user:article:collection:get", "user:article:item:get"];
    
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

    #[ORM\PreUpdate]
    public function setAuthorAndMentionState(): void
    {
        if(count($this->articleMentions) > 0){
            $this->isMentionedPerson = true;
        }else{
            $this->isMentionedPerson = false;
        }
        if(count($this->articles) > 0){
            $this->isAuthor = true;
        }else{
            $this->isAuthor = false;
        }
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ,...self::IN_ARTICLE, ...self::USER_UPDATE, ...self::USER_POST])]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ,...self::IN_ARTICLE, ...self::USER_UPDATE, ...self::USER_POST])]
    private ?string $lastName = null;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'authors', cascade:["persist"])]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ArticleMention::class)]
    private Collection $articleMentions;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawFullName = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_UPDATE, ...self::USER_POST])]
    private ?int $age = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_UPDATE, ...self::USER_POST])]
    private ?string $gender = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAuthor = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isMentionedPerson = null;


    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->articleMentions = new ArrayCollection();
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    #[Groups([...self::USER_READ])]
    public function getIsAuthor(): bool 
    {
        return $this->articles->count() > 0;
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
            $article->addAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            $article->removeAuthor($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ArticleMention>
     */
    public function getArticleMentions(): Collection
    {
        return $this->articleMentions;
    }

    public function addArticleMention(ArticleMention $articleMention): self
    {
        if (!$this->articleMentions->contains($articleMention)) {
            $this->articleMentions->add($articleMention);
            $articleMention->setPerson($this);
        }

        return $this;
    }

    public function removeArticleMention(ArticleMention $articleMention): self
    {
        if ($this->articleMentions->removeElement($articleMention)) {
            // set the owning side to null (unless already changed)
            if ($articleMention->getPerson() === $this) {
                $articleMention->setPerson(null);
            }
        }

        return $this;
    }

    public function getRawFullName(): ?string
    {
        return $this->rawFullName;
    }

    public function setRawFullName(?string $rawFullName): self
    {
        $this->rawFullName = $rawFullName;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsAuthor(): ?bool
    {
        return $this->isAuthor;
    }

    public function setIsAuthor(?bool $isAuthor): self
    {
        $this->isAuthor = $isAuthor;

        return $this;
    }

    public function isIsMentionedPerson(): ?bool
    {
        return $this->isMentionedPerson;
    }

    public function setIsMentionedPerson(?bool $isMentionedPerson): self
    {
        $this->isMentionedPerson = $isMentionedPerson;

        return $this;
    }
}

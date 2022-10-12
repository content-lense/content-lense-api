<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ApiResource]
class Person
{

    const USER_READ = ["user:person:collection:get", "user:person:item:get"];
    const IN_ARTICLE = ["user:article:collection:get", "user:article:item:get"];
    
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ,...self::IN_ARTICLE])]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ,...self::IN_ARTICLE])]
    private ?string $lastName = null;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'authors')]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ArticleMention::class)]
    private Collection $articleMentions;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawFullName = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $gender = null;

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
}

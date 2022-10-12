<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\State\ArticleProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'ipartial'])]
#[ApiResource(processor: ArticleProcessor::class)]
class Article
{
    const USER_READ = ["user:article:collection:get", "user:article:item:get"];
    const USER_POST = ["user:article:collection:post"];

    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?string $url = null;

    #[ORM\Column(nullable:true)]
    #[Groups([...self::USER_READ])]
    private ?int $version = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?string $language = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_POST])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_POST])]
    private ?string $abstract = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    private ?Organisation $organisation = null;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'articles')]
    #[Groups([...self::USER_READ])]
    private Collection $authors;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_POST])]
    private ?string $text = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleAnalysisResult::class, orphanRemoval: true)]
    #[Groups([...self::USER_READ])]
    private Collection $articleAnalysisResults;


    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleMention::class)]
    #[Groups([...self::USER_READ])]
    private Collection $mentionedPersons;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->articleAnalysisResults = new ArrayCollection();
        $this->mentionedPersons = new ArrayCollection();
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

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

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(?string $abstract): self
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Person $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        return $this;
    }

    public function removeAuthor(Person $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return Collection<int, ArticleAnalysisResult>
     */
    public function getArticleAnalysisResults(): Collection
    {
        return $this->articleAnalysisResults;
    }

    public function addArticleAnalysisResult(ArticleAnalysisResult $articleAnalysisResult): self
    {
        if (!$this->articleAnalysisResults->contains($articleAnalysisResult)) {
            $this->articleAnalysisResults->add($articleAnalysisResult);
            $articleAnalysisResult->setArticle($this);
        }

        return $this;
    }

    public function removeArticleAnalysisResult(ArticleAnalysisResult $articleAnalysisResult): self
    {
        if ($this->articleAnalysisResults->removeElement($articleAnalysisResult)) {
            // set the owning side to null (unless already changed)
            if ($articleAnalysisResult->getArticle() === $this) {
                $articleAnalysisResult->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ArticleMention>
     */
    public function getMentionedPersons(): Collection
    {
        return $this->mentionedPersons;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Filter\CountFilter;
use App\Repository\ArticleTopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Filter\MultipleFieldSearchFilter;

#[ORM\Entity(repositoryClass: ArticleTopicRepository::class)]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(MultipleFieldSearchFilter::class, properties: [
    "name"
])]
#[ApiFilter(CountFilter::class, properties: ['articles'])]
#[ApiFilter(PropertyFilter::class)]
#[ApiResource]
class ArticleTopic
{
    const USER_READ = ["user:articletopic:collection:get", "user:articletopic:item:get"];
    const USER_CREATE = ["user:articletopic:collection:post"];
    const USER_UPDATE = ["user:articletopic:item:put"];
    const IN_ARTICLE = ["user:article:item:get"];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([...self::USER_READ, ...self::USER_UPDATE, ...self::USER_CREATE, ...self::IN_ARTICLE])]
    #[Assert\NotBlank()]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'articleTopics')]
    #[Groups([...self::USER_UPDATE])]
    #[MaxDepth(1)]
    private Collection $articles;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_UPDATE, ...self::USER_CREATE])]
    private array $whitelist = [];

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups([...self::USER_READ, ...self::USER_UPDATE])]
    private array $blacklist = [];

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->whitelist = [];
        $this->blacklist = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    #[Groups([...self::USER_READ])]
    public function getArticleCount(): int
    {
        return $this->articles->count();
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        $this->articles->removeElement($article);

        return $this;
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function addWhitelist(string $keyword): self
    {
        $this->whitelist[] = $keyword;

        return $this;
    }

    public function setWhitelist(array $whitelist): self
    {
        $this->whitelist = $whitelist;

        return $this;
    }

    public function addBlacklist(string $keyword): self
    {
        $this->blacklist[] = $keyword;

        return $this;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    public function setBlacklist(array $blacklist): self
    {
        $this->blacklist = $blacklist;

        return $this;
    }
}

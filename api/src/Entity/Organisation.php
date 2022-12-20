<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\RefreshApiTokenController;
use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[ApiResource(operations: [
    new Put(
        name: 'refreshApiToken', 
        security: "is_granted('ROLE_ADMIN')",
        uriTemplate: '/organisations/{id}/refresh-api-token', 
        controller: RefreshApiTokenController::class,
        normalizationContext: [
            "groups" => ["refresh_api_token"]
        ],
        denormalizationContext: [
            "groups" => ["refresh_api_token"]
        ]
    ),
    new Get(),
    new Post(),
])]
class Organisation
{

    const ADMIN_READ = ["admin:organisation:item:get"];
    const ADMIN_UPDATE = ["admin:organisation:item:put"];
   
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
    #[Groups([...self::ADMIN_READ, "refresh_api_token"])]
    private ?string $apiToken = null;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Article::class)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: AnalysisMicroservice::class, orphanRemoval: true)]
    private Collection $analysisMicroservices;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: ArticleSource::class, orphanRemoval: true)]
    private Collection $articleSources;

    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Webhook::class)]
    private Collection $webhooks;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->analysisMicroservices = new ArrayCollection();
        $this->articleSources = new ArrayCollection();
        $this->webhooks = new ArrayCollection();
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

    public function refreshApiToken(): self
    {
        $this->setApiToken(md5(random_bytes(10)));
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

    /**
     * @return Collection<int, ArticleSource>
     */
    public function getArticleSources(): Collection
    {
        return $this->articleSources;
    }

    public function addArticleSource(ArticleSource $articleSource): self
    {
        if (!$this->articleSources->contains($articleSource)) {
            $this->articleSources->add($articleSource);
            $articleSource->setOrganisation($this);
        }

        return $this;
    }

    public function removeArticleSource(ArticleSource $articleSource): self
    {
        if ($this->articleSources->removeElement($articleSource)) {
            // set the owning side to null (unless already changed)
            if ($articleSource->getOrganisation() === $this) {
                $articleSource->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Webhook>
     */
    public function getWebhooks(): Collection
    {
        return $this->webhooks;
    }

    public function addWebhook(Webhook $webhook): self
    {
        if (!$this->webhooks->contains($webhook)) {
            $this->webhooks->add($webhook);
            $webhook->setOrganisation($this);
        }

        return $this;
    }

    public function removeWebhook(Webhook $webhook): self
    {
        if ($this->webhooks->removeElement($webhook)) {
            // set the owning side to null (unless already changed)
            if ($webhook->getOrganisation() === $this) {
                $webhook->setOrganisation(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AnalysisMicroserviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: AnalysisMicroserviceRepository::class)]
#[ApiResource]
class AnalysisMicroservice
{
    const ADMIN_READ = ["admin:analysismicroservice:collection:get", "admin:analysismicroservice:item:get"];
    const ADMIN_UPDATE = ["admin:analysismicroservice:item:put"];
    const ADMIN_CREATE = ["admin:analysismicroservice:collection:post"];
    
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::ADMIN_READ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::ADMIN_READ])]
    private ?string $endpoint = null;

    #[ORM\ManyToOne(inversedBy: 'analysisMicroservices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([...self::ADMIN_READ])]
    private ?Organisation $organisation = null;

    #[ORM\OneToMany(mappedBy: 'analysisMicroservice', targetEntity: ArticleAnalysisResult::class, orphanRemoval: true)]

    private Collection $articleAnalysisResults;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::ADMIN_READ])]
    private array $headers = [];

    #[ORM\Column]
    #[Groups([...self::ADMIN_READ])]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::ADMIN_READ])]
    private ?bool $autoRunForNewArticles = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([...self::ADMIN_READ])]
    private ?string $method = null;

    #[ORM\ManyToMany(targetEntity: Webhook::class, mappedBy: 'runAfterAnalyses')]
    private Collection $webhooks;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $postProcessors = [];

    #[ORM\Column(nullable: true)]
    private array $additionalPayload = [];

    public function __construct()
    {
        $this->articleAnalysisResults = new ArrayCollection();
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

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): self
    {
        $this->endpoint = $endpoint;

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
            $articleAnalysisResult->setAnalysisMicroservice($this);
        }

        return $this;
    }

    public function removeArticleAnalysisResult(ArticleAnalysisResult $articleAnalysisResult): self
    {
        if ($this->articleAnalysisResults->removeElement($articleAnalysisResult)) {
            // set the owning side to null (unless already changed)
            if ($articleAnalysisResult->getAnalysisMicroservice() === $this) {
                $articleAnalysisResult->setAnalysisMicroservice(null);
            }
        }

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isAutoRunForNewArticles(): ?bool
    {
        return $this->autoRunForNewArticles;
    }

    public function setAutoRunForNewArticles(?bool $autoRunForNewArticles): self
    {
        $this->autoRunForNewArticles = $autoRunForNewArticles;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

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
            $webhook->addRunAfterAnalysis($this);
        }

        return $this;
    }

    public function removeWebhook(Webhook $webhook): self
    {
        if ($this->webhooks->removeElement($webhook)) {
            $webhook->removeRunAfterAnalysis($this);
        }

        return $this;
    }

    public function getPostProcessors(): array
    {
        return $this->postProcessors;
    }

    public function setPostProcessors(array $postProcessors): self
    {
        $this->postProcessors = $postProcessors;

        return $this;
    }

    public function getAdditionalPayload(): array
    {
        return $this->additionalPayload;
    }

    public function setAdditionalPayload(?array $additionalPayload): self
    {
        $this->additionalPayload = $additionalPayload;

        return $this;
    }
}

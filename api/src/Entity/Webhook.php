<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\WebhookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ApiResource]
class Webhook
{

    const ADMIN_READ = ["admin:webhook:collection:get", "admin:webhook:item:get"];
    const ADMIN_UPDATE = ["admin:webhook:item:put"];
    const ADMIN_CREATE = ["admin:webhook:collection:post"];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private ?bool $runOnNewArticle = null;

    #[ORM\ManyToMany(targetEntity: AnalysisMicroservice::class, inversedBy: 'webhooks')]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private Collection $runAfterAnalyses;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private ?string $endpoint = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private ?string $name = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private array $logs = [];

    #[ORM\ManyToOne(inversedBy: 'webhooks')]
    private ?Organisation $organisation = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::ADMIN_READ,...self::ADMIN_CREATE,...self::ADMIN_UPDATE])]
    private ?bool $isActive = null;

    public function __construct()
    {
        $this->runAfterAnalyses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRunOnNewArticle(): ?bool
    {
        return $this->runOnNewArticle;
    }

    public function setRunOnNewArticle(?bool $runOnNewArticle): self
    {
        $this->runOnNewArticle = $runOnNewArticle;

        return $this;
    }

    /**
     * @return Collection<int, AnalysisMicroservice>
     */
    public function getRunAfterAnalyses(): Collection
    {
        return $this->runAfterAnalyses;
    }

    public function addRunAfterAnalysis(AnalysisMicroservice $runAfterAnalysis): self
    {
        if (!$this->runAfterAnalyses->contains($runAfterAnalysis)) {
            $this->runAfterAnalyses->add($runAfterAnalysis);
        }

        return $this;
    }

    public function removeRunAfterAnalysis(AnalysisMicroservice $runAfterAnalysis): self
    {
        $this->runAfterAnalyses->removeElement($runAfterAnalysis);

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
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

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function addLogMessage(string $message): self
    {
        $this->logs[] = $message;

        return $this;
    }
    public function setLogs(?array $logs): self
    {
        $this->logs = $logs;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleSourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleSourceRepository::class)]
#[ApiResource]
class ArticleSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $importIntervalInMinutes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastUpdatedAt = null;

    #[ORM\Column(nullable: true)]
    private array $mappingConfig = [];

    #[ORM\ManyToOne(inversedBy: 'articleSources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getImportIntervalInMinutes(): ?int
    {
        return $this->importIntervalInMinutes;
    }

    public function setImportIntervalInMinutes(?int $importIntervalInMinutes): self
    {
        $this->importIntervalInMinutes = $importIntervalInMinutes;

        return $this;
    }

    public function getLastUpdatedAt(): ?\DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(?\DateTimeInterface $lastUpdatedAt): self
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }

    public function getMappingConfig(): array
    {
        return $this->mappingConfig;
    }

    public function setMappingConfig(?array $mappingConfig): self
    {
        $this->mappingConfig = $mappingConfig;

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
}

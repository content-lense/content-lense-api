<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleSourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArticleSourceRepository::class)]
#[ApiResource]
class ArticleSource
{

    const USER_READ = ["user:articlesource:collection:get", "user:artiarticlesourcecle:item:get"];
    const USER_POST = ["user:articlesource:collection:post"];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([...self::USER_READ,...self::USER_READ])]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ,...self::USER_READ])]
    private ?int $importIntervalInMinutes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([...self::USER_READ,...self::USER_READ])]
    private ?\DateTimeInterface $lastUpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'articleSources')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([...self::USER_READ,...self::USER_READ])]
    private ?Organisation $organisation = null;

    #[ORM\Column(nullable: true, options: ["jsonb" => true])]
    private array $mappingConfig = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([...self::USER_READ,...self::USER_READ])]
    private ?string $url = null;

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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}

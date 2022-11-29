<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Enums\ArticleAnalysisStatus;
use App\Repository\ArticleAnalysisResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: ArticleAnalysisResultRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ["article"])]
#[ApiResource]
class ArticleAnalysisResult
{

    const USER_READ = ["user:articleanalysisresult:collection:get", "user:articleanalysisresult:item:get"];

    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\ManyToOne(inversedBy: 'articleAnalysisResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([...self::USER_READ])]
    private ?AnalysisMicroservice $analysisMicroservice = null;

    #[ORM\Column(nullable: true)]
    private array $rawResult = [];

    #[ORM\ManyToOne(inversedBy: 'articleAnalysisResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([...self::USER_READ])]
    private ?Article $article = null;

    #[ORM\Column(type: 'string', enumType: ArticleAnalysisStatus::class, options: ["default" => ArticleAnalysisStatus::PUSHED])]
    #[Groups([...self::USER_READ])]
    public ArticleAnalysisStatus $status;

    public function getStatus(): ArticleAnalysisStatus
    {
        return $this->status;
    }

    public function setStatus(ArticleAnalysisStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    #[Groups([...self::USER_READ])]
    public function getAnalysisMicroserviceName(): ?string
    {
        if ($this->analysisMicroservice) {
            return $this->analysisMicroservice->getName();
        }
        return null;
    }

    public function getAnalysisMicroservice(): ?AnalysisMicroservice
    {
        return $this->analysisMicroservice;
    }

    public function setAnalysisMicroservice(?AnalysisMicroservice $analysisMicroservice): self
    {
        $this->analysisMicroservice = $analysisMicroservice;

        return $this;
    }

    public function getRawResult(): array
    {
        return $this->rawResult;
    }

    public function setRawResult(?array $rawResult): self
    {
        $this->rawResult = $rawResult;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}

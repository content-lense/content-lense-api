<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleAnalysisResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: ArticleAnalysisResultRepository::class)]
#[ApiResource]
class ArticleAnalysisResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator("doctrine.uuid_generator")]
    #[ORM\Column(type: 'uuid', unique: true)]
    private $id = null;

    #[ORM\ManyToOne(inversedBy: 'articleAnalysisResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnalysisMicroservice $analysisMicroservice = null;

    #[ORM\Column(nullable: true)]
    private array $rawResult = [];

    #[ORM\ManyToOne(inversedBy: 'articleAnalysisResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    public function getId(): ?UuidV6
    {
        return $this->id;
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

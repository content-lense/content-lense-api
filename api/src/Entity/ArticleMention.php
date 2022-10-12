<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticleMentionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArticleMentionRepository::class)]
#[ApiResource]
class ArticleMention
{
    const IN_ARTICLE = ["user:article:collection:get", "user:article:item:get"];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'articleMentions')]
    #[Groups([...self::IN_ARTICLE])]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'mentionedPersons')]
    private ?Article $article = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::IN_ARTICLE])]
    private ?int $mentionCount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

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

    public function getMentionCount(): ?int
    {
        return $this->mentionCount;
    }

    public function setMentionCount(?int $mentionCount): self
    {
        $this->mentionCount = $mentionCount;

        return $this;
    }
}

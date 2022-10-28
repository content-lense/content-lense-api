<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\ArticleComplexityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiFilter(RangeFilter::class, properties: ["wienerSachtextIndex"])]
#[ApiFilter(SearchFilter::class, properties: ['part' => 'exact'])]
#[ApiFilter(PropertyFilter::class)]
#[ORM\Entity(repositoryClass: ArticleComplexityRepository::class)]
#[ApiResource]
class ArticleComplexity
{

    const USER_READ = ["user:articlecomplexity:collection:get", "user:articlecomplexity:item:get"];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?float $meanCharsPerWord = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?int $medianCharsPerWord = null;

    #[ORM\Column(nullable: true)]
    private ?int $medianWordsPerSentence = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalChars = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalLetters = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalSentences = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalSyllables = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalUniqueWords = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalWords = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalWordsLongerThanThreeSyllables = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalSingleSyllableWords = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?float $readingTimeInMinutes = null;

    #[ORM\Column(nullable: true)]
    #[Groups([...self::USER_READ])]
    private ?float $wienerSachtextIndex = null;

    #[ORM\ManyToOne(inversedBy: 'complexities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([...self::USER_READ])]
    private ?string $part = null;

    #[ORM\Column(nullable: true)]
    private ?float $meanWordsPerSentence = null;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getMeanCharsPerWord(): ?float
    {
        return $this->meanCharsPerWord;
    }

    public function setMeanCharsPerWord(?float $meanCharsPerWord): self
    {
        $this->meanCharsPerWord = $meanCharsPerWord;

        return $this;
    }

    public function getMedianCharsPerWord(): ?int
    {
        return $this->medianCharsPerWord;
    }

    public function setMedianCharsPerWord(?int $medianCharsPerWord): self
    {
        $this->medianCharsPerWord = $medianCharsPerWord;

        return $this;
    }

    public function getMedianWordsPerSentence(): ?int
    {
        return $this->medianWordsPerSentence;
    }

    public function setMedianWordsPerSentence(?int $medianWordsPerSentence): self
    {
        $this->medianWordsPerSentence = $medianWordsPerSentence;

        return $this;
    }

    public function getTotalChars(): ?int
    {
        return $this->totalChars;
    }

    public function setTotalChars(?int $totalChars): self
    {
        $this->totalChars = $totalChars;

        return $this;
    }

    public function getTotalLetters(): ?int
    {
        return $this->totalLetters;
    }

    public function setTotalLetters(?int $totalLetters): self
    {
        $this->totalLetters = $totalLetters;

        return $this;
    }

    public function getTotalSentences(): ?int
    {
        return $this->totalSentences;
    }

    public function setTotalSentences(?int $totalSentences): self
    {
        $this->totalSentences = $totalSentences;

        return $this;
    }

    public function getTotalSyllables(): ?int
    {
        return $this->totalSyllables;
    }

    public function setTotalSyllables(?int $totalSyllables): self
    {
        $this->totalSyllables = $totalSyllables;

        return $this;
    }

    public function getTotalUniqueWords(): ?int
    {
        return $this->totalUniqueWords;
    }

    public function setTotalUniqueWords(?int $totalUniqueWords): self
    {
        $this->totalUniqueWords = $totalUniqueWords;

        return $this;
    }

    public function getTotalWords(): ?int
    {
        return $this->totalWords;
    }

    public function setTotalWords(?int $totalWords): self
    {
        $this->totalWords = $totalWords;

        return $this;
    }

    public function getTotalWordsLongerThanThreeSyllables(): ?int
    {
        return $this->totalWordsLongerThanThreeSyllables;
    }

    public function setTotalWordsLongerThanThreeSyllables(?int $totalWordsLongerThanThreeSyllables): self
    {
        $this->totalWordsLongerThanThreeSyllables = $totalWordsLongerThanThreeSyllables;

        return $this;
    }

    public function getTotalSingleSyllableWords(): ?int
    {
        return $this->totalSingleSyllableWords;
    }

    public function setTotalSingleSyllableWords(?int $totalSingleSyllableWords): self
    {
        $this->totalSingleSyllableWords = $totalSingleSyllableWords;

        return $this;
    }

    public function getReadingTimeInMinutes(): ?float
    {
        return $this->readingTimeInMinutes;
    }

    public function setReadingTimeInMinutes(?float $readingTimeInMinutes): self
    {
        $this->readingTimeInMinutes = $readingTimeInMinutes;

        return $this;
    }

    public function getWienerSachtextIndex(): ?float
    {
        return $this->wienerSachtextIndex;
    }

    public function setWienerSachtextIndex(?float $wienerSachtextIndex): self
    {
        $this->wienerSachtextIndex = $wienerSachtextIndex;

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

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(string $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getMeanWordsPerSentence(): ?float
    {
        return $this->meanWordsPerSentence;
    }

    public function setMeanWordsPerSentence(?float $meanWordsPerSentence): self
    {
        $this->meanWordsPerSentence = $meanWordsPerSentence;

        return $this;
    }
}

<?php

namespace App\Controller;

use App\Entity\ArticleComplexity;
use Container1fBD7WE\getArticleComplexityRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleComplexityBoundaryController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    function buildQuery(string $field, string $order)
    {
        return floatval($this->em->createQueryBuilder()->select('a.' . $field)->from(ArticleComplexity::class, 'a')->orderBy('a.' . $field, $order)->setMaxResults(1)->getQuery()->getSingleColumnResult()[0]);
    }

    #[Route('/article_complexity/boundary', name: 'app_article_complexity_boundary')]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'wienerSachtextIndex' => [$this->buildQuery('wienerSachtextIndex', 'ASC'), $this->buildQuery('wienerSachtextIndex', 'DESC')],
            'readingTimeInMinutes' => [$this->buildQuery('readingTimeInMinutes', 'ASC'), $this->buildQuery('readingTimeInMinutes', 'DESC')],
            'totalSentences' => [$this->buildQuery('totalSentences', 'ASC'), $this->buildQuery('totalSentences', 'DESC')],
            'totalWords' => [$this->buildQuery('totalWords', 'ASC'), $this->buildQuery('totalWords', 'DESC')],
            'totalChars' => [$this->buildQuery('totalChars', 'ASC'), $this->buildQuery('totalChars', 'DESC')],
            'meanWordsPerSentence' => [$this->buildQuery('meanWordsPerSentence', 'ASC'), $this->buildQuery('meanWordsPerSentence', 'DESC')],
            'meanCharsPerWord' => [$this->buildQuery('meanCharsPerWord', 'ASC'), $this->buildQuery('meanCharsPerWord', 'DESC')],


        ]);
    }
}

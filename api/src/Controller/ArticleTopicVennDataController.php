<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleComplexity;
use App\Entity\ArticleTopic;
use Container1fBD7WE\getArticleComplexityRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleTopicVennDataController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/article_topics_venn', name: 'app_article_topics_venn')]
    public function index(EntityManagerInterface $em, Request $request): JsonResponse
    {

        $limit = $request->query->get("limit", 6);

        $qb = $em->createQueryBuilder();


        // Get n(limit) topics ordered by number of articles associated
        $query = "SELECT a0_.id AS article_topic_id, a0_.name, (SELECT COUNT(*) FROM article_topic_article a1_ WHERE a1_.article_topic_id = a0_.id) AS number_of_articles FROM article_topic a0_ ORDER BY number_of_articles DESC LIMIT :limit";
        $stmt = $em->getConnection()->prepare($query);
        $articleTopics = $stmt->executeQuery(["limit" => $limit])->fetchAllAssociativeIndexed();
        dump($articleTopics);
        $ids = [];
        $returnObj = [];
        $topics = [];
        $topicNames = array_map(fn($a) => $a["name"], $articleTopics);
        foreach($articleTopics as $topicId => $topicObj){
            $topics[$topicObj["name"]] = $topicObj["number_of_articles"];
            $topic = $em->getRepository(ArticleTopic::class)->find($topicId);
    

            foreach ($topic->getArticles() as $article) {
                $ids[] = $article->getId();

                $key = implode(";", $article->getArticleTopics()->filter(fn($t) => in_array($t->getName(),$topicNames))->map(fn ($t) => $t->getName())->toArray());
                if (array_key_exists($key, $topics)) {
                    $topics[$key]++;
                } else {
                    $topics[$key] = 1;
                }
                foreach ($article->getArticleTopics()->filter(fn($t) => in_array($t->getName(),$topicNames))->map(fn ($t) => $t->getName()) as $topic) {
                    if (array_key_exists($topic, $topics)) {
                        $topics[$topic]++;
                    } else {
                        $topics[$topic] = 1;
                    }
                }
            }
        }
        

        foreach ($topics as $key => $topic) {
            array_push($returnObj, array("key" => explode(";", $key), "data" => $topic));
        }

        return new JsonResponse($returnObj);
    }
}

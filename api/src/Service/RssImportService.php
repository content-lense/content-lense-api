<?php

namespace App\Service;

use App\Entity\Article;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RssImportService
{

    private $em;
    private $client;
    private $postProcessorService;

    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, PostProcessorService $postProcessorService)
    {
        $this->em = $em;
        $this->client = $client;
        $this->postProcessorService = $postProcessorService;
    }

    public function importFromUrl($url)
    {

        // Receive XML content from url:
        $resp = $this->client->request("GET", $url);
        $xml = $resp->getContent();

        // Parse XML content:
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $articles = json_decode($json, TRUE);
        foreach ($articles["entry"] as $_article) {
            $article = new Article();
            $article->setTitle($_article["title"]);
            $article->setUrl($_article["link"]['@attributes']['href']);
            $article->setPublishedAt(DateTime::createFromFormat(DateTime::ISO8601, $_article["published"]));
            $article->addAuthor($this->postProcessorService->getOrCreatePerson($_article["author"]["name"]));
            $crawler = new Crawler($_article["summary"]);
            $content = $crawler->filterXPath("//div[contains(@class, 'formatted')]");

            $article->setText($content->text());
            $this->em->persist($article);
        }
        $this->em->flush();
    }
}

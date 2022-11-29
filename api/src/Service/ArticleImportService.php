<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleAnalysisResult;
use App\Entity\ArticleSource;
use App\Entity\Organisation;
use App\Enums\ArticleAnalysisStatus;
use App\Message\ApplyAnalysisMicroserviceOnArticleMessage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArticleImportService
{

    private $em;
    private $client;
    private $postProcessorService;
    private $bus;

    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, PostProcessorService $postProcessorService, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->client = $client;
        $this->postProcessorService = $postProcessorService;
        $this->bus = $bus;
    }

    public static function ReturnPath($input, array $path)
    {
        $stringPath = "";
        foreach ($path as $step) {
            $stringPath .= " > " . $step;
            if (array_key_exists($step, $input)) {
                $input = $input[$step];
            } else {
                throw new Exception("Invalid path " . $stringPath);
            }
        }
        return $input;
    }

    public function sendArticleToPostProcessors(Organisation $organisation, Article $article)
    {
        $services = $organisation->getAnalysisMicroservices();
        foreach ($services as $service) {
            $result = new ArticleAnalysisResult();
            
            $result->setStatus($service->getIsActive() ? ArticleAnalysisStatus::PUSHED : ArticleAnalysisStatus::DISABLED)->setArticle($article)->setAnalysisMicroservice($service);
            $this->em->persist($result);
            $this->em->flush();
            if ($service->getIsActive() && $service->isAutoRunForNewArticles()) {
                $this->bus->dispatch(new ApplyAnalysisMicroserviceOnArticleMessage($article->getId(), $service->getId()));
            }
        }
    }

    public function importArticleSource(ArticleSource $articleSource)
    {
        // Receive content from url:
        $resp = $this->client->request("GET", $articleSource->getUrl());
        $content = $resp->getContent();

        if ($articleSource->getType() === ArticleSource::TYPE_RSS) {
            $createdArticles = $this->importRssXml($content, $articleSource);
        }

        foreach ($createdArticles as $article) {
            $this->sendArticleToPostProcessors($articleSource->getOrganisation(), $article);
        }
    }

    public function importRssXml($xml, ArticleSource $articleSource)
    {
        $createdArticles = [];
        // Parse XML content:
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $articles = json_decode($json, TRUE);

        // Get root:
        $mappingConfig = $articleSource->getMappingConfig();
        $fieldMapping = $mappingConfig["fieldMapping"];
        $articles = self::ReturnPath($articles, $mappingConfig["startFromPath"]);
        foreach ($articles as $data) {
            $article = new Article();
            if (array_key_exists("pathToAuthorName", $fieldMapping) && $fieldMapping["pathToAuthorName"] !== "") {
                if (array_key_exists("authorsRoot", $fieldMapping) && $fieldMapping["authorsRoot"] !== "") {
                    $authorsRoot = $data[$fieldMapping["authorsRoot"]];
                    foreach ($authorsRoot as $authorData) {
                        $authorName = self::ReturnPath($authorData, $fieldMapping["pathToAuthorName"]);
                        $article->addAuthor($this->postProcessorService->getOrCreatePerson($authorName));
                    }
                } else {
                    $authorName = self::ReturnPath($data, $fieldMapping["pathToAuthorName"]);
                    $article->addAuthor($this->postProcessorService->getOrCreatePerson($authorName));
                }
            }
            if (array_key_exists("fields", $fieldMapping) && is_array($fieldMapping["fields"])) {
                foreach ($fieldMapping["fields"] as $field) {
                    if (!array_key_exists("fieldInDatabase", $field) || !array_key_exists("pathInPayload", $field)) {
                        continue;
                    }
                    $dbField = $field["fieldInDatabase"];
                    $value = self::ReturnPath($data, $field["pathInPayload"]);
                    if (array_key_exists("isDate", $field) && $field["isDate"]) {
                        $value = DateTime::createFromFormat(DateTime::ISO8601, $value);
                    }
                    call_user_func(array($article, 'set' . ucfirst($dbField)), $value);
                }
            }

            if (array_key_exists("textExtraction", $mappingConfig) && is_array($mappingConfig["textExtraction"])) {
                $textExtractionRules = $mappingConfig["textExtraction"];
                if (array_key_exists("pathToText", $textExtractionRules)) {
                    $text = self::ReturnPath($data, $textExtractionRules["pathToText"]);
                    if (array_key_exists("xFilterPath", $textExtractionRules)) {
                        $crawler = new Crawler($text);
                        $text = $crawler->filterXPath($textExtractionRules["xFilterPath"])->text();
                    }

                    $article->setText($text);
                }
            }
            $this->em->persist($article);
            $createdArticles[] = $article;
        }
        $this->em->flush();
        return $createdArticles;
    }
}

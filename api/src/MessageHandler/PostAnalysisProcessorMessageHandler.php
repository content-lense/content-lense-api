<?php

namespace App\MessageHandler;

use App\Entity\ArticleAnalysisResult;
use App\Entity\ArticleMention;
use App\Enums\ArticleAnalysisStatus;
use App\Message\PostAnalysisProcessorMessage;
use App\Service\PostProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PostAnalysisProcessorMessageHandler implements MessageHandlerInterface
{
    private $em;
    private $postProcessor;
    private $clien;
    public function __construct(EntityManagerInterface $em, PostProcessorService $postProcessor, HttpClientInterface $client)
    {
        $this->em = $em;
        $this->postProcessor = $postProcessor;
        $this->client = $client;
    }
    
    public function __invoke(PostAnalysisProcessorMessage $message)
    {
        /** @var ArticleAnalysisResult */
        $result = $this->em->getRepository(ArticleAnalysisResult::class)->find($message->getArticleAnalysisResultId());
        if(!$result){
            throw new UnrecoverableMessageHandlingException("Article analysis result not found");
        }

        $result->setStatus(ArticleAnalysisStatus::POST_PROCESSING);
        $this->em->persist($result);
        $this->em->flush();

        $article = $result->getArticle();
        $processorName = $message->getProcessorName();
        
        switch($processorName){
            case PostProcessorService::STORE_MENTIONED_PEOPLE: 
                $this->postProcessor->storeMentionedPeople($article, $result->getRawResult());
                break;
            case PostProcessorService::STORE_TEXT_COMPLEXITY: 
                $this->postProcessor->storeTextComplexity($article, $result->getRawResult());
                break;
            case PostProcessorService::STORE_TOPIC_DETECTION: 
                $this->postProcessor->storeTopicDetection($article, $result->getRawResult());
                break;
            case PostProcessorService::STORE_SENTIMENT: 
                $this->postProcessor->storeSentiment($article, $result->getRawResult());
                break;
        }

        $result->setStatus(ArticleAnalysisStatus::DONE);
        $this->em->persist($result);
        $this->em->flush();

        // Check for webhooks
        $organisation = $result->getAnalysisMicroservice()->getOrganisation();
        if($organisation){
            $webhooks = $organisation->getWebhooks();
            if(count($webhooks) > 0) {
                foreach($webhooks as $webhook){
                    if(!$webhook->getIsActive()) continue;
                    $webhook->addLogMessage("Called");
                    $url = $webhook->getEndpoint();
                    $res = $this->client->request("POST", $url, [
                        "headers" => [
                            "content-type" => "application/json"
                        ],
                        "json" => $result->getRawResult()
                    ]);
                    $this->em->persist($webhook);
                }
                $this->em->flush();
            }
        }

    }
}


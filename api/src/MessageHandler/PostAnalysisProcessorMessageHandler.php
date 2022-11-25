<?php

namespace App\MessageHandler;

use App\Entity\ArticleAnalysisResult;
use App\Entity\ArticleAnalysisStatus;
use App\Entity\ArticleMention;
use App\Message\PostAnalysisProcessorMessage;
use App\Service\PostProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PostAnalysisProcessorMessageHandler implements MessageHandlerInterface
{
    private $em;
    private $postProcessor;
    public function __construct(EntityManagerInterface $em, PostProcessorService $postProcessor)
    {
        $this->em = $em;
        $this->postProcessor = $postProcessor;
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
    }
}


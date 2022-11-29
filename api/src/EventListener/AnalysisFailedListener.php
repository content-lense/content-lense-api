<?php

// src/EventListener/AnalysisFailedListener.php
namespace App\EventListener;

use App\Entity\ArticleAnalysisResult;
use App\Enums\ArticleAnalysisStatus;
use App\Message\ApplyAnalysisMicroserviceOnArticleMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

#[AsEventListener(event: WorkerMessageFailedEvent::class, method:"onWorkerMessageFailedEvent")]
class AnalysisFailedListener
{
    private EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onWorkerMessageFailedEvent(WorkerMessageFailedEvent $event){
        /** @var ApplyAnalysisMicroserviceOnArticleMessage */
        $msg = $event->getEnvelope()->getMessage();
        if(get_class($msg) === ApplyAnalysisMicroserviceOnArticleMessage::class){
            $result = $this->em->getRepository(ArticleAnalysisResult::class)->findOneBy(["article" => $msg->getArticleId(), "analysisMicroservice" => $msg->getAnalysisMicroserviceId()]);
            if(!$result){
                return;
            }
        }

        if(!$event->willRetry()){
            $result->setStatus(ArticleAnalysisStatus::FAILED);
        }else{
            $result->setStatus(ArticleAnalysisStatus::RETRIED_PROCESSING);
        }
        $this->em->persist($result);
        $this->em->flush();
    }
}
<?php

namespace App\MessageHandler;

use App\Entity\AnalysisMicroservice;
use App\Entity\Article;
use App\Entity\ArticleAnalysisResult;
use App\Entity\Person;
use App\Message\ApplyAnalysisMicroserviceOnArticleMessage;
use App\Message\PostAnalysisProcessorMessage;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApplyAnalysisMicroserviceOnArticleMessageHandler implements MessageHandlerInterface
{

    private $em;
    private $client;
    private $bus;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->client = $client;
        $this->bus = $bus;
    }
    public function __invoke(ApplyAnalysisMicroserviceOnArticleMessage $message)
    {
        $article = $this->em->getRepository(Article::class)->find($message->getArticleId());
        if (!$article) {
            throw new UnrecoverableMessageHandlingException("Article not found");
        }

        /** @var AnalysisMicroservice */
        $service = $this->em->getRepository(AnalysisMicroservice::class)->find($message->getAnalysisMicroserviceId());
        if (!$service) {
            throw new UnrecoverableMessageHandlingException("Unknown microservice");
        }

        $client = $this->client->withOptions([
            'headers' => $service->getHeaders()
        ]);

        $payload = [
            "id" => $article->getId(),
            "heading" => $article->getTitle(),
            "summary" => $article->getAbstract(),
            "authors" => $article->getAuthors()->map(fn (Person $person) => sprintf("%s %s", $person->getFirstName(), $person->getLastName())),
            "body" => $article->getText()
        ];
        $payload = array_merge($payload, $service->getAdditionalPayload() ?? []);
        
        $response = $client->request($service->getMethod(), $service->getEndpoint(), [
            'json' => $payload
        ]);

        if ($response->getStatusCode() == 200) {
            dump(sprintf("Received result from micrsoservice %s from endpoint %s", $service->getName(), $service->getEndpoint()));
            try {
                $content = json_decode($response->getContent(), true);

                $result = new ArticleAnalysisResult();
                $result->setArticle($article)->setRawResult($content)->setAnalysisMicroservice($service);
                $this->em->persist($result);
                $this->em->flush();

                foreach ($service->getPostProcessors() as $processorName) {
                    $msg = new PostAnalysisProcessorMessage($result->getId(), $processorName);
                    $this->bus->dispatch($msg);
                }
            } catch (Exception $e) {
                dump($e);
            }
        } else {
            dump(sprintf("Status code %s from micrsoservice %s from endpoint %s", $response->getStatusCode(), $service->getName(), $service->getEndpoint()));
        }
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Person;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\ArticleTopic;
use App\Entity\Webhook;
use App\Service\ArticleImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ArticleProcessor implements ProcessorInterface
{
    private $decorated;

    private $bus;
    private $em;
    private $security;
    private $articleImportService;
    private $client;
    private $serializer;
    public function __construct(ProcessorInterface $decorated, MessageBusInterface $bus, EntityManagerInterface $em, Security $security, ArticleImportService $articleImportService, HttpClientInterface $client, SerializerInterface $serializer)
    {
        $this->bus = $bus;
        $this->security = $security;
        $this->em = $em;
        $this->decorated = $decorated;
        $this->articleImportService = $articleImportService;
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Article */
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);
        $rawAuthors = $result->getRawAuthors();
        if($rawAuthors && $rawAuthors !== ""){
            $authors = explode(",",$rawAuthors);
            $authors = array_unique($authors);
            $authors = array_values(array_filter($authors, fn($value) => !empty($value)));
            foreach($authors as $author){
                $names = explode(" ", trim($author));
                $names = array_values(array_filter($names, fn($value) => !empty($value)));
                $names = array_map(fn($value) => trim($value), $names);
                $tmpAuthor = new Person();
                $tmpAuthor->setFirstName("")->setLastName("")->setRawFullName("");
                $tmpAuthor->setRawFullName(trim($author));
                if(count($names) > 0){
                    $tmpAuthor->setFirstName(trim($names[0]));
                }
                if(count($names) > 1){
                    $tmpAuthor->setLastName(trim($names[1]));
                }
                $this->em->persist($tmpAuthor);
                $result->addAuthor($tmpAuthor);
                $this->em->persist($result);
            }
            $this->em->flush();
        }

        $rawTopics = $result->getRawTopics();
        if($rawTopics && $rawTopics !== ""){
            $topics = explode(",",$rawTopics);
            $topics = array_map(fn($value) => trim($value), $topics);
            $topics = array_unique($topics);
            $topics = array_values(array_filter($topics, fn($value) => !empty($value)));
            foreach($topics as $topic){
                $tmpTopic = $this->em->getRepository(ArticleTopic::class)->findOneBy(["name" => $topic]);
                if(!$tmpTopic){
                    $tmpTopic = new ArticleTopic();
                    $tmpTopic->setName($topic);
                    $this->em->persist($tmpTopic);
                }
                $result->addArticleTopic($tmpTopic);
                $this->em->persist($result);
            }
            $this->em->flush();
            
        }


        /** @var User */
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }
        
        // TODO: which organisation should we take the services from? for now, just take the first:
        $organisation = $user->getOrganisations()->get(0);
        $this->articleImportService->sendArticleToPostProcessors($organisation, $result);

        // Check for webhooks
        $webhooks = $this->em->getRepository(Webhook::class)->findBy(["isActive" => true, "organisation" => $organisation, "runOnNewArticle" => true]);
        if(count($webhooks) > 0) {
            foreach($webhooks as $webhook){
                $webhook->addLogMessage("Called for new article");
                $url = $webhook->getEndpoint();
                $res = $this->client->request("POST", $url, [
                    "headers" => [
                        "content-type" => "application/json"
                    ],
                    'verify_peer' => false,
                    'verify_host' => false,
                    "json" => $this->serializer->serialize($result, "json", ["groups" => "user:article:item:get"])
                ]);
                $this->em->persist($webhook);
            }
            $this->em->flush();
        }
        return $result;
    }
}

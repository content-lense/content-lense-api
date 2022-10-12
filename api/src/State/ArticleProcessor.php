<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Message\ApplyAnalysisMicroserviceOnArticleMessage;
use App\Repository\AnalysisMicroserviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

final class ArticleProcessor implements ProcessorInterface
{
    private $decorated;

    private $bus;
    private $em;
    private $security;
    public function __construct(ProcessorInterface $decorated, MessageBusInterface $bus, EntityManagerInterface $em, Security $security )
    {
        $this->bus = $bus;
        $this->security = $security;
        $this->em = $em;
        $this->decorated = $decorated;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);
        /** @var User */
        $user = $this->security->getUser();
        if(!$user){
            return;
        }
        // TODO: which organisation should we take the services from? for now, just take the first:
        $organisation = $user->getOrganisations()->get(0);
        $services = $organisation->getAnalysisMicroservices();
        foreach($services as $service){
            if($service->isIsActive() && $service->isAutoRunForNewArticles()){
                dump("Send article to analysis: ".$service->getEndpoint());
                $this->bus->dispatch(new ApplyAnalysisMicroserviceOnArticleMessage($result->getId(),$service->getId()));
            }
            
        } 
        return $result;
    }

}
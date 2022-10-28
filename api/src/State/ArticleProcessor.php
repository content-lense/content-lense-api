<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Message\ApplyAnalysisMicroserviceOnArticleMessage;
use App\Repository\AnalysisMicroserviceRepository;
use App\Service\ArticleImportService;
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
    private $articleImportService;
    public function __construct(ProcessorInterface $decorated, MessageBusInterface $bus, EntityManagerInterface $em, Security $security, ArticleImportService $articleImportService)
    {
        $this->bus = $bus;
        $this->security = $security;
        $this->em = $em;
        $this->decorated = $decorated;
        $this->articleImportService = $articleImportService;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);
        /** @var User */
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        // TODO: which organisation should we take the services from? for now, just take the first:
        $organisation = $user->getOrganisations()->get(0);
        $this->articleImportService->sendArticleToPostProcessors($organisation, $result);
        return $result;
    }
}

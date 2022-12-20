<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

final class UserProcessor implements ProcessorInterface
{
    private $decorated;
    private $entityManager;
    private $security;
    private $userPasswordEncoder;
    private $mailer;


    public function __construct(ProcessorInterface $decorated, EntityManagerInterface $entityManager, Security $security, UserPasswordHasherInterface $userPasswordEncoder, MailerService $mailer)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->decorated = $decorated;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->mailer = $mailer;
    }

    /**
     * @param User $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
                
        if ($data->getPlainPassword() && $data->getRepeatPassword() && $data->getPlainPassword() == $data->getRepeatPassword()) {
            $data->setPassword(
                $this->userPasswordEncoder->hashPassword($data, $data->getPlainPassword())
            );
            $data->eraseCredentials();
            
        }

        if ($data->getPlainPassword() != $data->getRepeatPassword()) {
            throw new BadRequestHttpException("Passwords do not match");
        }



        if ($this->security->isGranted("ROLE_ADMIN")) {
            if ($this->security->getUser()->getUserIdentifier() == $data->getUserIdentifier()) {
                // Do not allow to disable my own admin user
                $data->setIsActive(true);
            }
        }

        if($operation->getName() === "reset_password") {
            // auto confirm user if password is reset, this should only happen for invites
            $data->setIsConfirmed(true);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        if($operation instanceof Post){
            $this->mailer->sendConfirmRegistrationMail($data);
        }

        
        return $data;
    }
    
}

<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UpdateUserSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['checkForRequestPasswordToken', EventPriorities::POST_DESERIALIZE]
            ]
        ];
    }


    public function checkForRequestPasswordToken(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $previousData = $request->attributes->get("previous_data");
        $data = $request->attributes->get("data");
        $controller = $request->attributes->get("_controller");

        if (!$data instanceof User || (Request::METHOD_PATCH !== $method && Request::METHOD_PUT !== $method) ) {
            return;
        }
        $payload = json_decode($request->getContent(), true);

        if(array_key_exists("resetPasswordToken", $payload)){
            if($previousData->getResetPasswordToken() === null){
                throw new AccessDeniedHttpException("No token set, require one first using /auth/reset-password");
            }

            if($previousData->getResetPasswordToken() == $payload["resetPasswordToken"]){
                if(!array_key_exists("password", $payload)){
                    throw new UnprocessableEntityHttpException("No password given.");
                }
            }else{
                throw new AccessDeniedHttpException("Invalid password reset token.");
            }

            $data->setResetPasswordToken(null);
        }
    }



}

<?php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Doctrine\ORM\EntityManagerInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator
{

    private $em;
    public function __construct(EntityManagerInterface $em)
    {  
        $this->em = $em;
    }
    
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        // Organisation by api token:
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(["apiToken" => $apiToken]);
        if(!$organisation){
            throw new CustomUserMessageAuthenticationException('Invalid API token');
        }

        $owner = $organisation->getOwner()->getEmail();
        $badge = new UserBadge($owner, function (string $userIdentifier) {
            return $this->em->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);
        });
        return new SelfValidatingPassport($badge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtEventsSubscriber implements EventSubscriberInterface
{
    private $security;
    private $serializer;
    private $em;
    private $router;
    private $requestStack;

    public function __construct(
        Security $security,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $router,
        RequestStack $requestStack
    ) {
        $this->security = $security;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJwtCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        if ($user) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneBy(["email" => $user->getUserIdentifier()]);
            $payload = $event->getData();
            $payload["id"] = $user->getId();
            $event->setData($payload);
        }
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
    }
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $routeName = $request->attributes->get("_route");
        if ($routeName == "authentication_token") {
            /** @var User */
            $user = $event->getUser();
            $user->setNumberOfLogins($user->getNumberOfLogins() + 1);
        }
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // If jwt_hp is missing but jwt_s exists, remove jwt_s (this happens if we logout a user by removing the jwt_hp cookie)
        if (
            $request->cookies->has("jwt_s") &&
            !$request->cookies->has("jwt_hp")
        ) {
            $request->cookies->remove("jwt_s");
        }

        // If jwt_s is missing but jwt_hp exists, remove jwt_hp (this happens because of the cookie origin policy)
        if (
            $request->cookies->has("jwt_hp") &&
            !$request->cookies->has("jwt_s")
        ) {
            $request->cookies->remove("jwt_hp");
        }


        // If we have a refresh_token cookie, set it as content for refresh-library to work correctly
        $routeName = $request->attributes->get("_route");
        if (
            $routeName == "gesdinet_jwt_refresh_token" &&
            $request->cookies->has("refresh_token")
        ) {
            $content = [
                "refresh_token" => $request->cookies->get("refresh_token"),
            ];
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                json_encode($content)
            );
            // Make sure the content type is JSON as otherwise Gesdinet\JWTRefreshTokenBundle\Request\RequestRefreshToken.php will fail getting the token from the body content
            $request->headers->set("CONTENT_TYPE", "application/json");
        } else {
        }
    }

    public function exchangeJsonTokenToHttpOnlyCookie(ResponseEvent $event)
    {

        $request = $event->getRequest();
        $routeName = $request->attributes->get("_route");
        // Take care only for login and refresh routes:
        if (
            $routeName != "gesdinet_jwt_refresh_token" &&
            $routeName != "authentication_token"
        ) {
            return;
        }

        $response = $event->getResponse();
        $content = json_decode($response->getContent(), true);

        // If a refresh_token is set in the response, remove it and replace it with a httpOnly Cookie
        if (is_array($content) && array_key_exists("refresh_token", $content)) {
            $refresh_token = $content["refresh_token"];

            if ($routeName == "authentication_token") {
                $user = $this->security->getUser();
                $res = $this->serializer->serialize($user, "json", [
                    "groups" => "owner",
                ]);
            } else {
                $res = json_encode([
                    "result" => "success",
                ]);
            }

            $response->setContent($res);
            $expire = new \DateTime("now");
            $expire->modify("+1 month");
            $tmpPath = $this->router->generate(
                "gesdinet_jwt_refresh_token",
                [],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
            $response->headers->setCookie(
                Cookie::create(
                    "refresh_token",
                    $refresh_token,
                    $expire,
                    $tmpPath,
                    null,
                    true,
                    true,
                    false,
                    Cookie::SAMESITE_STRICT
                )
            );
        }
    }

    public function onRefreshToken(RefreshEvent $event)
    {
        $refreshToken = $event->getPreAuthenticatedToken();

        $user = $refreshToken->getUser();

        /** @var User */
        $appUser = $this->em
            ->getRepository(User::class)
            ->findOneBy(["email" => $user->getUserIdentifier()]);
        // Any conditions to refresh the token:
        if ($appUser && !$appUser->getIsActive()) {
            throw new AccessDeniedHttpException("Inactive.");
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            "gesdinet.refresh_token" => [["onRefreshToken", 20]],
            "lexik_jwt_authentication.on_jwt_created" => [["onJwtCreated", 20]],
            "lexik_jwt_authentication.on_authentication_success" => [
                ["onAuthenticationSuccess", 20],
            ],
            "lexik_jwt_authentication.on_authentication_failure" => [
                ["onAuthenticationFailure", 20],
            ],
            KernelEvents::REQUEST => [["onKernelRequest", 10]],
            KernelEvents::RESPONSE => [
                ["exchangeJsonTokenToHttpOnlyCookie", 20],
            ],
        ];
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{

    #[Route('/auth/me', name: 'me')]
    public function me(SerializerInterface $serializer): Response
    {
        /** @var User */
        $authenticatedUser = $this->getUser();
        if (!$authenticatedUser->getIsActive()) {
            $resp = $this->createClearCookieResponse();
            $resp->setStatusCode(401);
            return $resp;
        }
        $json = $serializer->serialize(
            $authenticatedUser,
            'jsonld',
            ['groups' => 'me']
        );
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/auth/reset-password', name: 'request_reset_passsword', methods: ['POST'])]
    public function requestResetPasssword(Request $request, EntityManagerInterface $em, MailerService $mailer): Response
    {
        $content = json_decode($request->getContent(), true);
        if (!array_key_exists("email", $content)) return new Response();
        $user = $em->getRepository(User::class)->findOneBy(["email" => $content["email"]]);
        if (!$user) {
            return new Response();
        }
        $mailer->sendResetPasswordToken($user);
        return new Response();
    }

    #[Route('/auth/logout', name: 'logout')]
    public function logout(EntityManagerInterface $em): Response
    {

        /** @var User */
        $authenticatedUser = $this->getUser();

        if (null === $authenticatedUser) {
            return new Response();
        }

        $conn = $em->getConnection();
        $conn->executeQuery("DELETE FROM refresh_tokens WHERE username = :email", ["email" => $authenticatedUser->getUsername()]);
        return $this->createClearCookieResponse();
    }

    private function createClearCookieResponse()
    {
        $response = new Response();
        $response->headers->clearCookie("refresh_token", "/auth/refresh_token");
        $response->headers->clearCookie("jwt_hp");
        $response->headers->clearCookie("jwt_s");
        return $response;
    }
}

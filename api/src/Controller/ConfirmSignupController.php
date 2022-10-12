<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ConfirmSignupController extends AbstractController
{

    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(User $data, Request $request): Response
    {
        if($request->query->get("token") == $data->getConfirmationToken()){
            $data->setIsConfirmed(true);
            $this->em->persist($data);
            $this->em->flush();
        }
        return new Response();
    }
}

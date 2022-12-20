<?php
namespace App\Controller;

use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsController]
class RefreshApiTokenController extends AbstractController
{

    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(Organisation $organisation, Request $request): Organisation
    {
        $prevData = $request->attributes->get("previous_data");
        $decoded = json_decode($request->getContent(), true);
        if($decoded["apiToken"] !== $prevData->getApiToken()){
            throw new AccessDeniedHttpException("Invalid token given");
        }
        $organisation->refreshApiToken();
        $this->em->persist($organisation);
        $this->em->flush();
        return $organisation;
    }
}

<?php
namespace App\DataFixtures;

use App\Entity\AnalysisMicroservice;
use App\Entity\Person;
use App\Entity\Organisation;
use App\Entity\User;
use App\Service\PostProcessorService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

class DevAnalysisMicroservicesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "analysis_microservices"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class,
            DevUserFixtures::class
        ];
    }

    public function load(ObjectManager $m): void
    {        
        $organisation = $this->getReference(DevOrganisationFixtures::ORGANISATION);
        $service = new AnalysisMicroservice();
        $service->setName("Recognize mentioned people")->setEndpoint("http://host.docker.internal:5555/articles")->setIsActive(true);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_MENTIONED_PEOPLE]);
        $m->persist($service);
        $m->flush();
    }
}

<?php
namespace App\DataFixtures;

use App\Entity\AnalysisMicroservice;
use App\Entity\Person;
use App\Entity\Organisation;
use App\Entity\User;
use App\Entity\Webhook;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

class DevWebhookFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "webhooks"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class,
        ];
    }

    public function load(ObjectManager $m): void
    {        
        $organisation = $this->getReference(DevOrganisationFixtures::ORGANISATION);
        $service = new Webhook();
        $service->setName("Forward to wordpress")
        ->setEndpoint("https://envup9p51mjxq.x.pipedream.net/")
        ->setIsActive(true)->setLogs(["Created"])
        ->setRunOnNewArticle(true)
        ->setOrganisation($organisation);
        $m->persist($service);
        $m->flush();
    }
}

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
        $service->setName("Recognize mentioned people")->setEndpoint("http://host.docker.internal:5555/articles")->setIsActive(false);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_MENTIONED_PEOPLE]);
        $m->persist($service);

        $service = new AnalysisMicroservice();
        $service->setName("Analyze text complexity")->setEndpoint("http://host.docker.internal:5001/articles")->setIsActive(false);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_TEXT_COMPLEXITY]);
        $m->persist($service);
        $m->flush();

        $service = new AnalysisMicroservice();
        $service->setName("Analyze topic detection")->setEndpoint("http://host.docker.internal:5002/articles")->setIsActive(true);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_TOPIC_DETECTION]);
        $service->setAdditionalPayload([
            "customTopics" => [
                "Home",
                "Ticker",
                "RSS",
                "Forum",
                "Zusatzdienste",
                "Stellenmarkt",
                "Karrierewelt",
                "Impressum",
                "Leitbild",
                "Jobs",
                "Datenschutz",
                "Cookies & Tracking",
                "Werbung",
                "Ansicht",
                "Wirtschaft",
                "Amazon",
                "Apple",
                "Bitcoin",
                "Facebook",
                "Google",
                "IBM",
                "Intel",
                "Microsoft",
                "Mozilla",
                "Samsung",
                "Sony",
                "Yahoo",
                "Wikipedia",
                "Wissenschaft",
                "3D-Drucker",
                "Augmented Reality",
                "Cloud Computing",
                "Drohne",
                "Künstliche Intelligenz",
                "Roboter",
                "Raumfahrt",
                "Quantencomputer",
                "Supercomputer",
                "Virtuelle Realität",
                "Automobil",
                "Elektroauto",
                "E-Bike",
                "Security",
                "Hacker",
                "Sicherheitslücke",
                "TOR-Netzwerk",
                "Verschlüsselung",
                "Chaos Computer Club",
                "Netzpolitik",
                "Datenschutz",
                "DSGVO",
                "NSA",
                "Überwachung",
                "Vorratsdatenspeicherung",
                "Europäische Union",
                "Icann",
                "Games",
                "Spieletests",
                "Oculus Rift",
                "Spielekonsole",
                "Playstation 5",
                "Xbox Series X",
                "Steam",
                "Hausautomation",
                "Smarthome",
                "LED-Lampe",
                "DIY - Do it yourself",
                "Anleitung",
                "Raspberry Pi",
                "Mobil",
                "Akku",
                "Android",
                "Cyanogenmod",
                "iOS",
                "Windows Phone",
                "Mobilfunk",
                "Oneplus",
                "Smartphone",
                "Smartwatch",
                "Tablet",
                "Wearable",
                "Streaming",
                "Whatsapp",
                "Software",
                "Browser",
                "Chrome",
                "Firefox",
                "Bittorrent",
                "Linux",
                "Mac OSX",
                "Windows",
                "Open Source",
                "API",
                "Softwareentwicklung",
                "Programmiersprache",
                "HTML5",
                "Docker",
                "Golem.de",
                "Tests",
                "PC-Hardware",
                "Prozessoren",
                "Grafikkarten",
                "Eingabegeräte",
                "Speichermedien",
                "4K",
                "Mini-PC",
                "Router",
                "USB",
                "USB-C",
                "Digitale Fotografie"
            ],
            "totalTopics" => 3
        ]);
        $m->persist($service);
        $m->flush();
    }
}

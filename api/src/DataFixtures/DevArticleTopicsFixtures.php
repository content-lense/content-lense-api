<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleComplexity;
use App\Entity\ArticleTopic;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

class DevArticleTopicsFixtures extends Fixture implements FixtureGroupInterface
{

    public const FIXTURE_TOPICS = [
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
    ];

    public static function getGroups(): array
    {
        return ["dev", "article_topics"];
    }


    public function load(ObjectManager $m): void
    {
        $faker = Factory::create();

        foreach (self::FIXTURE_TOPICS as $topicName) {
            $topic = new ArticleTopic();
            $topic->setName($topicName);
            for ($i = 0; $i < rand(0, 4); $i++) {
                $topic->addWhitelist($faker->word());
            }
            for ($i = 0; $i < rand(0, 4); $i++) {
                $topic->addBlacklist($faker->word());
            }
            $m->persist($topic);
            $this->addReference($topicName, $topic);
        }

        $m->flush();
    }
}

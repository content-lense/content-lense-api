<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleSource;
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

class DevArticleSourcesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "article_sources"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class
        ];
    }

    public function load(ObjectManager $m): void
    {
        $faker = Factory::create();

        $orga = $this->getReference(DevOrganisationFixtures::ORGANISATION);
        $source = new ArticleSource();
        $golem = "https://rss.golem.de/rss_sub_media.php?token=";
        if (array_key_exists("GOLEM_TOKEN", $_SERVER)) {
            $golem .= $_SERVER["GOLEM_TOKEN"];
        }
        $source->setImportIntervalInMinutes(60)->setOrganisation($orga)
            ->setType(ArticleSource::TYPE_RSS)->setUrl($golem)
            ->setMappingConfig([
                "startFromPath" => ["entry"],
                "textExtraction" => [
                    "pathToText" => ["summary"],
                    "xFilterPath" => "//div[contains(@class, 'formatted')]"
                ],
                "fieldMapping" => [
                    "authorsRoot" => "", // if no root given, we expect a single author on the top level
                    "pathToAuthorName" => ["author", "name"],
                    "fields" => [
                        [
                            "fieldInDatabase" => "url",
                            "pathInPayload" => ["link", "@attributes", "href"],
                        ],
                        [
                            "isDate" => true,
                            "fieldInDatabase" => "publishedAt",
                            "pathInPayload" => ["published"]
                        ]
                    ]
                ],

            ]);
        $m->persist($source);
        dump("Note: You need to set the token of the fixture RSS if not in your .env.local!");
        $m->flush();
    }
}

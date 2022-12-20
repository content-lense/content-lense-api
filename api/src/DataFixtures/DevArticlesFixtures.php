<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleAnalysisResult;
use App\Entity\ArticleComplexity;
use App\Entity\ArticleMention;
use App\Entity\ArticleTopic;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\User;
use App\Enums\ArticleAnalysisStatus;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

class DevArticlesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "articles"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class,
            DevUserFixtures::class,
            DevPersonsFixtures::class,
            DevArticleTopicsFixtures::class,
            DevAnalysisMicroservicesFixtures::class
        ];
    }

    public function load(ObjectManager $m): void
    {
        $faker = Factory::create();

        $persons = $m->getRepository(Person::class)->findAll();
        for ($i = 0; $i < 750; $i++) {
            $a = new Article();
            $a->setAbstract($faker->sentences(1, true))->setLanguage("DE")->setPublishedAt($faker->dateTimeThisCentury())->setCreatedAt($faker->dateTimeThisYear())
                ->setTitle($faker->sentence(8))->setUrl($faker->url())->setVersion(1)->setImage($faker->imageUrl())->setText($faker->paragraphs(3, true))->setSentimentOfText($faker->numberBetween(1, 5));
            $a->setOrganisation($this->getReference(DevOrganisationFixtures::ORGANISATION));
            $firstAuthor = $persons[array_rand($persons, 1)];
            $a->addAuthor($firstAuthor);
            $firstAuthor->setIsAuthor(true);
            $secondAuthor = $persons[array_rand($persons, 1)];
            if ($faker->boolean() && $firstAuthor !== $secondAuthor) {
                $a->addAuthor($secondAuthor);
                $secondAuthor->setIsAuthor(true);
            }
            $mentionedPerson = $persons[array_rand($persons, 1)];
            if ($firstAuthor !== $mentionedPerson && $secondAuthor !== $mentionedPerson) {
                $mention = new ArticleMention();
                $mention->setArticle($a);
                $mention->setPerson($mentionedPerson);
                $m->persist($mention);
            }
            for ($k = 0; $k < rand(0, 3); $k++) {
                $topic = $this->getReference(DevArticleTopicsFixtures::FIXTURE_TOPICS[array_rand(DevArticleTopicsFixtures::FIXTURE_TOPICS)]);
                $a->addArticleTopic($topic);
            }
            foreach (DevAnalysisMicroservicesFixtures::MICROSERVICES as $microserviceName) {
                $microservice = $this->getReference($microserviceName);
                $articleAnalysisResult = new ArticleAnalysisResult();
                $articleAnalysisResult->setStatus($microservice->getIsActive() ? ArticleAnalysisStatus::PUSHED : ArticleAnalysisStatus::DISABLED);
                $articleAnalysisResult->setAnalysisMicroservice($microservice)->setArticle($a)->setRawResult([]);
                $m->persist($articleAnalysisResult);
            }


            $m->persist($a);
            $m->persist($firstAuthor);
            $m->persist($secondAuthor);
        }
        $m->flush();
    }
}

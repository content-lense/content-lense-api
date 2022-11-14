<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleComplexity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DevArticleComplexityFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "articlecomplexity"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class,
            DevUserFixtures::class,
            DevArticlesFixtures::class,
        ];
    }

    public function load(ObjectManager $m): void
    {
        $faker = Factory::create();


        $articles = $m->getRepository(Article::class)->findAll();
        for ($i = 0; $i < 50; $i++) {
            $a = new ArticleComplexity();
            $a->setTotalWords($faker->numberBetween(500, 5000))
                ->setReadingTimeInMinutes($faker->numberBetween(5, 50))
                ->setWienerSachtextIndex($faker->randomFloat(2, 0, 15))
                ->setTotalChars($faker->numberBetween(5000, 50000))
                ->setMeanCharsPerWord($faker->randomFloat(2, 2, 10))
                ->setTotalSentences($faker->numberBetween(50, 500))
                ->setPart("body")
                ->setMeanWordsPerSentence($faker->randomFloat(2, 0, 30))
                ->setArticle($articles[array_rand($articles, 1)]);
            $m->persist($a);
        }
        $m->flush();
    }
}

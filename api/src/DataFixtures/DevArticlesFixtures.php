<?php
namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleComplexity;
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
        ];
    }

    public function load(ObjectManager $m): void
    {        
        $faker = Factory::create();
       
        $persons = $m->getRepository(Person::class)->findAll();
        for($i = 0; $i<50;$i++){
            $a = new Article();
            $a->setAbstract($faker->sentences(1,true))->setLanguage("DE")->setPublishedAt($faker->dateTimeThisCentury())
            ->setTitle($faker->sentence(8))->setUrl($faker->url())->setVersion(1)->setImage($faker->imageUrl())->setText($faker->paragraphs(3, true));
            $a->setOrganisation($this->getReference(DevOrganisationFixtures::ORGANISATION));
            $firstAuthor = $persons[array_rand($persons,1)];
            $a->addAuthor($firstAuthor);
            $secondAuthor = $persons[array_rand($persons,1)];
            if($faker->boolean() && $firstAuthor !== $secondAuthor){
                $a->addAuthor($secondAuthor);
            }
            $m->persist($a);
        }
        $m->flush();
    }
}

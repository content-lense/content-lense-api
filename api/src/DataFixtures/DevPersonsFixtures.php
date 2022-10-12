<?php
namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\Organisation;
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

class DevPersonsFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "persons"];
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
        $faker = Factory::create();
       
        for($i = 0; $i<10;$i++){
            $p = new Person();
            $p->setFirstName($faker->firstName())->setLastName($faker->lastName());
            $m->persist($p);
        }
        $m->flush();
    }
}

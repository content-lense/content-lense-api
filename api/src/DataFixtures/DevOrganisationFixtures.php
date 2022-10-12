<?php

namespace App\DataFixtures;

use App\DataFixtures\Dev\DevUserFixtures;
use App\Entity\Organisation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DevOrganisationFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ["dev", "organisations"];
    }

    public function __construct(
        UserPasswordHasherInterface $pwdHasher
    ) {
        $this->pwdHasher = $pwdHasher;
    }

    public const ORGANISATION = 'admin-orga';
    public const ORGANISATION2 = 'second-orga';

    public function load(ObjectManager $m): void
    {
        // Creating the organisation has been moved to DevUserFixtures
        
    }
} 


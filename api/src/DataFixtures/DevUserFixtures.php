<?php
namespace App\DataFixtures;

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

class DevUserFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ["dev", "users"];
    }

    public function getDependencies()
    {
        return [
            DevOrganisationFixtures::class
        ];
    }

    private $pwdHasher;
    public function __construct(
        UserPasswordHasherInterface $pwdHasher
    ) {
        $this->pwdHasher = $pwdHasher;
    }
    public const ADMIN = 'admin@cl.com';
    public const USER = 'user@cl.com';
    public const ADMIN2 = 'admin2@cl.com';
    public const USER2 = 'user2@cl.com';
    public const DEMO_PASSWD = "demodemo";

    public function load(ObjectManager $m): void
    {        
        $organisation = new Organisation();
        $organisation->setName("ContentLense");
        $m->persist($organisation);
        $this->addReference(DevOrganisationFixtures::ORGANISATION, $organisation);

        $organisation2 = new Organisation();
        $organisation2->setName("MediaTechLab");
        $m->persist($organisation2);
        $this->addReference(DevOrganisationFixtures::ORGANISATION2, $organisation2);


        // ADMIN
        $admin = new User();
        $admin->setEmail(self::ADMIN)->setPassword(
            $this->pwdHasher->hashPassword($admin, self::DEMO_PASSWD)
        )->setRoles(["ROLE_ADMIN"])->setId(Uuid::fromString("1ed011a0-705f-6eb8-a2ff-9d1553360070"))
        ->setIsActive(true)->setIsConfirmed(true)->setConfirmationToken("abc");
        $m->persist($admin);

        $this->addReference(self::ADMIN, $admin);

        // USER
        $user = new User();
        $user->setEmail(self::USER)->setPassword(
            $this->pwdHasher->hashPassword($user, self::DEMO_PASSWD)
        )->setRoles(["ROLE_ADMIN"])->setId(Uuid::fromString("1ed011a0-705f-6eb8-a2ff-9d1553360071"))
        ->setIsActive(true)->setIsConfirmed(true)->setConfirmationToken("abc");
        $m->persist($user);

        $this->addReference(self::USER, $user);

        // admin2
        $admin2 = new User();
        $admin2->setEmail(self::ADMIN2)->setPassword(
            $this->pwdHasher->hashPassword($admin2, self::DEMO_PASSWD)
        )->setRoles(["ROLE_ADMIN"])->setId(Uuid::fromString("1ed011a0-705f-6eb8-a2ff-9d1553360080"))
        ->setIsActive(true)->setIsConfirmed(true)->setConfirmationToken("abc");
        $m->persist($admin2);

        $this->addReference(self::ADMIN2, $admin2);

        // USER 2
        $user2 = new User();
        $user2->setEmail(self::USER2)->setPassword(
            $this->pwdHasher->hashPassword($user2, self::DEMO_PASSWD)
        )->setRoles(["ROLE_ADMIN"])->setId(Uuid::fromString("1ed011a0-705f-6eb8-a2ff-9d1553360081"))
        ->setIsActive(true)->setIsConfirmed(true)->setConfirmationToken("abc");
        $m->persist($user2);
        $this->addReference(self::USER2, $user2);


        $organisation->setOwner($admin);
        $organisation->addMember($admin);
        $organisation->addMember($user);
        $organisation->setApiToken("33973585cd5f17cad05f1a09bb663f89");
        //$organisation->setApiToken(md5(random_bytes(10)));
        $m->persist($organisation);
        
        $organisation2->setOwner($admin2);
        $organisation2->addMember($admin2);
        $organisation2->addMember($user2);
        $organisation2->setApiToken(md5(random_bytes(10)));
        $m->persist($organisation2);
        $m->flush();
    }
}

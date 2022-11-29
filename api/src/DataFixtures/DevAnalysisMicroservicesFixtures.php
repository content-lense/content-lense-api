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

    const MENTIONED_PEOPLE = "mentioned_people";
    const TEXT_COMPLEXITY = "text_complexity";
    const TOPIC_DETECTION = "topic_detection";
    const SENTIMENT = "sentiment";
    const MICROSERVICES = [self::MENTIONED_PEOPLE, self::TEXT_COMPLEXITY, self::SENTIMENT, self::TOPIC_DETECTION];
    public function load(ObjectManager $m): void
    {
        $organisation = $this->getReference(DevOrganisationFixtures::ORGANISATION);
        $service = new AnalysisMicroservice();
        $service->setName("Recognize mentioned people")->setEndpoint("http://host.docker.internal:5555/articles")->setIsActive(false);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_MENTIONED_PEOPLE]);
        $m->persist($service);
        $this->addReference(self::MENTIONED_PEOPLE, $service);

        $service = new AnalysisMicroservice();
        $service->setName("Analyze text complexity")->setEndpoint("http://host.docker.internal:5001/articles")->setIsActive(false);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_TEXT_COMPLEXITY]);
        $m->persist($service);
        $m->flush();
        $this->addReference(self::TEXT_COMPLEXITY, $service);

        $service = new AnalysisMicroservice();
        $service->setName("Analyze topic detection")->setEndpoint("http://host.docker.internal:5002/articles")->setIsActive(false);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_TOPIC_DETECTION]);
        $service->setAdditionalPayload([
            "customTopics" => DevArticleTopicsFixtures::FIXTURE_TOPICS,
            "totalTopics" => 3
        ]);
        $m->persist($service);
        $m->flush();
        $this->addReference(self::TOPIC_DETECTION, $service);

        $service = new AnalysisMicroservice();
        $service->setName("Sentiment analysis")->setEndpoint("http://host.docker.internal:5003/articles")->setIsActive(true);
        $service->setOrganisation($organisation)->setMethod("POST")->setAutoRunForNewArticles(true);
        $service->setPostProcessors([PostProcessorService::STORE_SENTIMENT]);
        $m->persist($service);
        $m->flush();
        $this->addReference(self::SENTIMENT, $service);
    }
}

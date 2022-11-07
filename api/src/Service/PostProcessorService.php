<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleComplexity;
use App\Entity\ArticleMention;
use App\Entity\ArticleTopic;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostProcessorService
{

    public const STORE_MENTIONED_PEOPLE = "STORE_MENTIONED_PEOPLE";
    public const STORE_TEXT_COMPLEXITY = "STORE_TEXT_COMPLEXITY";
    public const STORE_TOPIC_DETECTION = "STORE_TOPIC_DETECTION";
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getOrCreatePerson($name): Person
    {
        $person = $this->em->getRepository(Person::class)->findOneBy(["rawFullName" => $name]);
        if ($person) {
            return $person;
        }
        $names = explode(" ", $name);
        $firstName = "";
        $lastName = "";
        if (count($names) >= 2) {
            $firstName = $names[0];
            $lastName = end($names);
        } else if (count($names) === 1) {
            $lastName = end($names);
        }
        $p = new Person();
        $p->setFirstName($firstName);
        $p->setLastName($lastName);
        $p->setRawFullName($name);
        $this->em->persist($p);
        $this->em->flush();
        return $p;
    }


    public function storeTextComplexity(Article $article, $result)
    {

        $parts = ["heading", "body", "summary"];
        $descriptives = "descriptives";
        $scores = "scores";
        foreach ($parts as $part) {
            if (array_key_exists($part, $result)) {
                dump("Store complexity for article " . $article->getId());
                $articleComplexity = new ArticleComplexity();
                $articleComplexity->setArticle($article);
                $articleComplexity->setPart($part);
                $articleComplexity->setMeanWordsPerSentence($result[$part][$descriptives]["meanWordsPerSentence"]);
                $articleComplexity->setMeanCharsPerWord($result[$part][$descriptives]["meanCharsPerWord"]);
                $articleComplexity->setMedianCharsPerWord($result[$part][$descriptives]["medianCharsPerWord"]);
                $articleComplexity->setMedianWordsPerSentence($result[$part][$descriptives]["medianWordsPerSentence"]);
                $articleComplexity->setTotalChars($result[$part][$descriptives]["totalChars"]);
                $articleComplexity->setTotalLetters($result[$part][$descriptives]["totalLetters"]);
                $articleComplexity->setTotalSentences($result[$part][$descriptives]["totalSyllables"]);
                $articleComplexity->setTotalUniqueWords($result[$part][$descriptives]["totalUniqueWords"]);
                $articleComplexity->setTotalWords($result[$part][$descriptives]["totalWords"]);
                $articleComplexity->setTotalWordsLongerThanThreeSyllables($result[$part][$descriptives]["totalWordsLongerThanThreeSyllables"]);
                $articleComplexity->setTotalSingleSyllableWords($result[$part][$descriptives]["totalSingleSyllableWords"]);

                $articleComplexity->setWienerSachtextIndex($result[$part][$scores]["wienerSachtextIndex"]);
                $articleComplexity->setReadingTimeInMinutes($result[$part][$scores]["readingTimeInMinutes"]);

                $this->em->persist($articleComplexity);
                
            } else {
                dump("Part does not exist in payload: " . $part);
            }
        }
        $this->em->flush();
    }

    public function storeMentionedPeople(Article $article, $result)
    {
        $parts = ["heading", "body", "summary"];
        $names = [];
        foreach ($parts as $part) {
            foreach ($result["result"][$part]["summary"] as $mention) {
                $name = $mention["name"];
                $person = $this->getOrCreatePerson($name);
                $person->setAge($mention["age"]);
                $person->setGender($mention["gender"]);
                $this->em->persist($person);
                $articleMention = $this->em->getRepository(ArticleMention::class)->findOneBy(["article" => $article, "person" => $person]);
                if ($articleMention) {
                    $articleMention->setMentionCount($articleMention->getMentionCount() + $mention["mentionCount"]);
                } else {
                    $articleMention = new ArticleMention();
                    $articleMention->setArticle($article)->setPerson($person)->setMentionCount($mention["mentionCount"]);
                }

                $this->em->persist($articleMention);
                $this->em->flush();
            }
        }
    }

    public function storeTopicDetection(Article $article, $result)
    {
        if(!array_key_exists("topics", $result) && is_array($result["topics"])){
            throw new Exception("No topics found in microservice response");
        }
        foreach($result["topics"] as $topic){
            /** @var ArticleTopic */
            $articleTopic = $this->em->getRepository(ArticleTopic::class)->findOneBy(["name" => $topic]);
            if(!$articleTopic){
                $articleTopic = new ArticleTopic();
                $articleTopic->setName($topic);
            }
            $articleTopic->addArticle($article);
            $this->em->persist($articleTopic);
        }

        $this->em->flush();   
    }
}

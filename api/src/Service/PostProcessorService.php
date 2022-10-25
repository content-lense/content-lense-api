<?php
namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleMention;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;
    
    public function __construct (EntityManagerInterface $em){
        $this->em = $em;
    }

    public function getOrCreatePerson($name): Person
    {
        $person = $this->em->getRepository(Person::class)->findOneBy(["rawFullName" => $name]);
        if($person){
            return $person;
        }
        $names = explode(" ",$name);
        $firstName = "";
        $lastName = "";
        if(count($names) >= 2){
            $firstName = $names[0];
            $lastName = end($names);
        }else if(count($names) === 1){
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


    public function storeTextComplexity(Article $article, $result){
        // TODO: store text complexity result in entity
    }

    public function storeMentionedPeople(Article $article, $result) 
    {
        $parts = ["heading", "body", "summary"];
        $names = [];
        foreach($parts as $part){
            foreach($result["result"][$part]["summary"] as $mention){    
                $name = $mention["name"];
                $person = $this->getOrCreatePerson($name);
                $person->setAge($mention["age"]);
                $person->setGender($mention["gender"]);
                $this->em->persist($person);
                $articleMention = $this->em->getRepository(ArticleMention::class)->findOneBy(["article" => $article, "person" => $person]);
                if($articleMention){
                    $articleMention->setMentionCount($articleMention->getMentionCount() + $mention["mentionCount"]);
                }else{
                    $articleMention = new ArticleMention();
                    $articleMention->setArticle($article)->setPerson($person)->setMentionCount($mention["mentionCount"]);
                }

                $this->em->persist($articleMention);
                $this->em->flush();
            }
        }
        
    }

}

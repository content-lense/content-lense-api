<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\MessengerMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MessengerMessageRepository::class, readOnly:true)]
#[ORM\Table(name:'messenger_messages')]
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext:[
        "groups" => ["messenger_messages_fields"]
    ]
)]
#[ApiFilter(PropertyFilter::class)]
class MessengerMessage
{
    // nextval('messenger_messages_id_seq'::regclass)
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:"IDENTITY")]
    #[ORM\Column(type:"bigint", nullable: true)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["messenger_messages_fields"])]
    private ?string $body = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headers = null;

    #[ORM\Column(length: 190, nullable: true)]
    #[Groups(["messenger_messages_fields"])]
    private ?string $queueName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["messenger_messages_fields"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["messenger_messages_fields"])]
    private ?\DateTimeInterface $availableAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["messenger_messages_fields"])]
    private ?\DateTimeInterface $deliveredAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }


    public function getBody(): ?array
    {
        if($this->body){
            return json_decode($this->body,true);
        }
        return $this->body;
        
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    #[Groups(["messenger_messages_fields"])]
    public function getType():?string 
    {
        if($this->headers){
            $headers = json_decode($this->headers,true);
            if(array_key_exists("type", $headers)){
                return $headers["type"];
            }
            
        }
    }

    #[Groups(["messenger_messages_fields"])]
    public function getExceptions():?array 
    {
        if($this->headers){
            $headers = json_decode($this->headers,true);
            if(array_key_exists("X-Message-Stamp-Symfony\\Component\\Messenger\\Stamp\\ErrorDetailsStamp", $headers)){
                $exceptionsTemp = $headers["X-Message-Stamp-Symfony\\Component\\Messenger\\Stamp\\ErrorDetailsStamp"];
                $exceptions = array_map(fn ($v) => $v->exceptionMessage, array_values(json_decode($exceptionsTemp)));                ;
                return $exceptions;
            }
        }
        return null;
    }

    public function getHeaders(): ?array
    {
        if($this->headers){
            return json_decode($this->headers,true);
        }
        return $this->headers;
    }

    public function setHeaders(?string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getQueueName(): ?string
    {
        return $this->queueName;
    }

    public function setQueueName(?string $queueName): self
    {
        $this->queueName = $queueName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAvailableAt(): ?\DateTimeInterface
    {
        return $this->availableAt;
    }

    public function setAvailableAt(?\DateTimeInterface $availableAt): self
    {
        $this->availableAt = $availableAt;

        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $deliveredAt): self
    {
        $this->deliveredAt = $deliveredAt;

        return $this;
    }
}

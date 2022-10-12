<?php

namespace App\Message;

use Symfony\Component\Uid\UuidV6;

final class ApplyAnalysisMicroserviceOnArticleMessage
{
    
     private $articleId;
     private $analysisMicroserviceId;

     public function __construct(UuidV6 $articleId, UuidV6 $analysisMicroserviceId)
     {
         $this->analysisMicroserviceId = $analysisMicroserviceId;
         $this->articleId = $articleId;
     }

    public function getAnalysisMicroserviceId(): UuidV6
    {
        return $this->analysisMicroserviceId;
    }

    public function getArticleId(): UuidV6
    {
        return $this->articleId;
    }
}

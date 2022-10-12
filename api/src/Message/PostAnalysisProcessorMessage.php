<?php

namespace App\Message;

use Symfony\Component\Uid\UuidV6;

final class PostAnalysisProcessorMessage
{

    private $articleAnalysisResultId;
    private $processorName;

    public function __construct(UuidV6 $articleAnalysisResultId,  string $processorName)
     {
        $this->articleAnalysisResultId = $articleAnalysisResultId;
        $this->processorName = $processorName;
     }

    public function getArticleAnalysisResultId(): UuidV6
    {
        return $this->articleAnalysisResultId;
    }

    public function getProcessorName(): string
    {
        return $this->processorName;
    }

}

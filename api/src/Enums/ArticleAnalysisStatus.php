<?php

namespace App\Enums;
enum ArticleAnalysisStatus: string
    {
        case PUSHED = "PUSHED";
        case PROCESSING = "PROCESSING";
        case PROCESSED =" PROCESSED";
        case POST_PROCESSING = "POST_PROCESSING";
        case DONE = "DONE";
    }
<?php

namespace App\Enums;
enum ArticleAnalysisStatus: string
    {
        case PUSHED = "PUSHED";
        case DISABLED = "DISABLED";
        case PROCESSING = "PROCESSING";
        case RETRIED_PROCESSING = "RETRIED_PROCESSING";
        case PROCESSED =" PROCESSED";
        case POST_PROCESSING = "POST_PROCESSING";
        case DONE = "DONE";
        case FAILED = "FAILED";
    }
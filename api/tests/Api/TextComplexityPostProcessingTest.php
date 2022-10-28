<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Article;
use App\Service\PostProcessorService;

class TextComplexityPostProcessingTest extends ApiTestCase
{
    public function testStoreTextComplexity(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) get post processor service
        $postProcessorService = $container->get(PostProcessorService::class);

        // (4) TODO: fake payload and test result of service
        /*$testPayload = [
            "body" => [
                "scores" => [
                    "wienerSachtext" => 4.5
                ]
            ]
        ];

        $article = new Article();
        $postProcessorService->storeTextComplexity($article, $testPayload);*/
    }
}

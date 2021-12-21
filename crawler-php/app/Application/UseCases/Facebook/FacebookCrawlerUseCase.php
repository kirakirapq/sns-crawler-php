<?php

namespace App\Application\UseCases\Facebook;

use App\Application\UseCases\SnsCrawlUseCase;

/**
 * FacebookCrawlerUseCase
 */
interface FacebookCrawlerUseCase extends SnsCrawlUseCase
{
    public function invokeCrawling(string $sns, string $title, string $language);
}

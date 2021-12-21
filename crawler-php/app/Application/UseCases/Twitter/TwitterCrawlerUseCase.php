<?php

namespace App\Application\UseCases\Twitter;

use App\Application\UseCases\SnsCrawlUseCase;

/**
 * TwitterCrawlerUseCase
 */
interface TwitterCrawlerUseCase extends SnsCrawlUseCase
{
    public function invokeCrawling(string $sns, string $title, string $language);
}

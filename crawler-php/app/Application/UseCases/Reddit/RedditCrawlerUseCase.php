<?php

namespace App\Application\UseCases\Reddit;

use App\Application\UseCases\SnsCrawlUseCase;

/**
 * RedditCrawlerUseCase
 */
interface RedditCrawlerUseCase extends SnsCrawlUseCase
{
    public function invokeCrawling(string $sns, string $title, string $language);
}

<?php

namespace App\Application\UseCases;

interface SnsCrawlUseCase
{
    public function invokeCrawling(string $sns, string $title, string $language);
}

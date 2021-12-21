<?php

namespace App\Application\InputData;

class SubRedditRequestData
{
    const BASE_URI = 'https://www.reddit.com/r/%s/new.json';

    public function getUri(string $id): string
    {
        return sprintf(self::BASE_URI, $id);
    }

    public function getOptions(): array
    {
        return [
            'headers' => [
                'Accept'        => 'application/json',
            ]
        ];
    }

    public function getMethod(): string
    {
        return RequestType::GET;
    }
}

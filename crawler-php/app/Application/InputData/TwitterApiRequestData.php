<?php

namespace App\Application\InputData;

use Illuminate\Support\Facades\Config;

class TwitterApiRequestData
{
    const BASE_URI = 'https://api.twitter.com/2/users/%s/mentions?tweet.fields=created_at&max_results=100';

    public function getUri(string $userId, string $paginationToken = null): string
    {
        if (is_null($paginationToken) === true) {
            return sprintf(self::BASE_URI, $userId);
        }

        $uri = sprintf(self::BASE_URI, $userId);

        return sprintf('%s&pagination_token=%s', $uri, $paginationToken);
    }

    public function getOptions(): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . Config::get('app.TWITTER_BEARER_TOKEN'),
                'Accept'        => 'application/json',
            ]
        ];
    }

    public function getMethod(): string
    {
        return RequestType::GET;
    }
}

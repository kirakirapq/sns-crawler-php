<?php

namespace App\Application\InputData;

use App\Entities\Reddit\Thread;

class SubRedditThreadCommentRequestData
{
    private Thread $thread;
    private string $url;

    public function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function getUri(int $limit = 1000): string
    {
        return $this->thread->getUrl($limit);
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

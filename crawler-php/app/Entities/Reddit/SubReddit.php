<?php

namespace App\Entities\Reddit;

use Illuminate\Support\Collection;

final class SubReddit
{
    const HOST_NAME = 'www.reddit.com';
    private string $appName;
    private ?Collection $threadList = null;

    public function __construct(array $response)
    {
        $threads = [];
        foreach ($response['data']['children'] as $children) {
            $this->appName = $children['data']['subreddit'];
            $title = $children['data']['title'];
            $text = $children['data']['selftext'];
            $url = $this->getUrl($children['data']['permalink']);

            if ($this->isThread($url) === true) {
                $threads[] = new Thread($title, $text, $url);
            }
        }
        $this->threadList = collect($threads);
    }

    public function getUrl($permalink): string
    {
        return sprintf('https://%s%s', self::HOST_NAME, $permalink);
    }

    /**
     * getThreadList
     *
     * @return ?Collection
     */
    public function getThreadList(): ?Collection
    {
        return $this->threadList;
    }

    /**
     * isThread
     *
     * @param  mixed $url
     * @return bool
     */
    public function isThread(string $url): bool
    {
        return parse_url($url, PHP_URL_HOST) === self::HOST_NAME;
    }
}

<?php

namespace App\Adapters;

use App\Application\InputData\SubRedditRequestData;
use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;

/**
 * RedditApiAdapter
 */
final class RedditApiAdapter
{
    /**
     * SubRedditRequestData
     *
     * @return void
     */
    static public function getSubRedditRequestData(): SubRedditRequestData
    {
        return new SubRedditRequestData();
    }

    /**
     * RedditApiRequestData
     *
     * @return void
     */
    static public function getCommentRequestData(Thread $thread): SubRedditThreadCommentRequestData
    {
        return new SubRedditThreadCommentRequestData($thread);
    }

    /**
     * getSubReddit
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function getSubReddit(array $httpResponse): SubReddit
    {
        return new SubReddit($httpResponse);
    }

    /**
     * getThread
     *
     * @param  mixed $httpResponse
     * @param  mixed $request
     * @return Thread
     */
    static public function getThread(array $httpResponse, SubRedditThreadCommentRequestData $request): Thread
    {
        $thread =  $request->getThread();

        $children = $httpResponse[1]['data']['children'];

        $thread->setComments($children);

        return $thread;
    }
}

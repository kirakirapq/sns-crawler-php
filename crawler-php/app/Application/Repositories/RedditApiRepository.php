<?php

namespace App\Application\Repositories;

use App\Application\InputData\SubRedditRequestData;
use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;

/**
 * RedditApiRepository
 */
interface RedditApiRepository
{
    public function getSubReddit(SubRedditRequestData $requestData, string $id): SubReddit;

    public function getComment(SubRedditThreadCommentRequestData $requestData): Thread;
}

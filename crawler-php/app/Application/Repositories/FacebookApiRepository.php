<?php

namespace App\Application\Repositories;

use App\Application\InputData\Facebook\FacebookRequestData;
use App\Entities\Facebook\FacebookDataList;

/**
 * RedditApiRepository
 */
interface FacebookApiRepository
{
    public function getFacebookDataList(FacebookRequestData $requestData): FacebookDataList;
}

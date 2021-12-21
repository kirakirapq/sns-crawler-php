<?php

namespace App\Application\Repositories;

use App\Entities\Twitter\TwitterMentionDataList;

/**
 * TwitterApiRepository
 */
interface TwitterApiRepository
{
    public function getMentions(string $userId, string $paginationToken = null): TwitterMentionDataList;
}

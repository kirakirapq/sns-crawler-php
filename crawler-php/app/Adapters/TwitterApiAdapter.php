<?php

namespace App\Adapters;

use App\Entities\Twitter\TwitterMentionDataList;

/**
 * TwitterApiAdapter
 * TwitterApiRepositoryがTwitterMentionDataListを取得
 */
final class TwitterApiAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function responseToMentionDataList(array $responseData): TwitterMentionDataList
    {
        return TwitterMentionDataList::getInstance($responseData['meta'], $responseData['data'] ?? []);
    }
}

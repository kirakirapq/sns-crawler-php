<?php

namespace App\Application\UseCases\Twitter;

use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use Illuminate\Support\Collection;

/**
 * TwitterApiUseCase
 */
interface TwitterApiUseCase
{
    /**
     * getLatestData
     *
     * @param  mixed $title
     * @param  mixed $language
     * @return LatestData|null
     */
    public function getLatestData(string $title, string $language): ?LatestData;

    /**
     * getTwitterMentionList
     *
     * @param  mixed $userId
     * @param  mixed $created_at
     * @return Collection
     */
    public function getTwitterMentionList(string $userId, ?Colmun $created_at = null): ?Collection;
}

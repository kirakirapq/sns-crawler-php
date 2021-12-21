<?php

namespace App\Application\UseCases\Facebook;

use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use Illuminate\Support\Collection;

/**
 * FacebookApiUseCase
 */
interface FacebookApiUseCase
{
    /**
     * getLatestData
     *
     * @param  mixed $title
     * @param  mixed $language
     * @return string
     */
    public function getLatestData(string $title, string $language): ?LatestData;

    /**
     * getMessageList
     *
     * @param  mixed $id
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getFeedList(string $title, string $language, ?Colmun $colmun = null): ?Collection;

    /**
     * getCommentList
     *
     * @param  mixed $threadList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getCommentList(string $title, string $language, Collection $threadList, ?Colmun $colmun = null): ?Collection;
}

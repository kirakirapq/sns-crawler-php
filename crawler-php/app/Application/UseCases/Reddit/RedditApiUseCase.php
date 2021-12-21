<?php

namespace App\Application\UseCases\Reddit;

use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use Illuminate\Support\Collection;

/**
 * RedditApiUseCase
 */
interface RedditApiUseCase
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
     * getThreadList
     *
     * @param  mixed $id
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getThreadList(string $id, ?Colmun $createdAt = null): ?Collection;

    /**
     * getCommentList
     *
     * @param  mixed $threadList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getCommentList(Collection $threadList, ?Colmun $createdAt = null): ?Collection;
}

<?php

namespace App\Application\UseCases\Reddit;

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
    public function getLatestData(string $title, string $language): ?string;

    /**
     * getThreadList
     *
     * @param  mixed $id
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getThreadList(string $id, $createdAt = null): ?Collection;

    /**
     * getCommentList
     *
     * @param  mixed $threadList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getCommentList(Collection $threadList, $createdAt = null): ?Collection;
}

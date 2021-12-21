<?php

namespace App\Application\UseCases\Facebook;

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
    public function getLatestData(string $title, string $language): ?string;

    /**
     * getMessageList
     *
     * @param  mixed $id
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getFeedList(string $title, string $language, $createdAt = null): ?Collection;

    /**
     * getCommentList
     *
     * @param  mixed $threadList
     * @param  mixed $createdAt
     * @return Collection
     */
    public function getCommentList(string $title, string $language, Collection $threadList, $createdAt = null): ?Collection;
}

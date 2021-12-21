<?php

namespace App\Application\UseCases\Twitter;

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
     * @return string|null
     */
    public function getLatestData(string $title, string $language): ?string;

    /**
     * getTwitterMentionList
     *
     * @param  mixed $userId
     * @param  mixed $created_at
     * @return Collection
     */
    public function getTwitterMentionList(string $userId, $created_at = null): ?Collection;
}

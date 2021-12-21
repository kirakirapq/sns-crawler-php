<?php

namespace App\Entities\Twitter;

class TwitterMetaData
{
    /**
     * __construct
     *
     * @param  mixed $meta
     * @return void
     */
    public function __construct(array $metaData)
    {
        $this->metaData = collect($metaData);
    }

    /**
     * getByKey
     *
     * @param  mixed $key
     * @return void
     */
    public function getByKey(string $key)
    {
        return $this->metaData->get($key);
    }

    /**
     * getResultCount
     *
     * @return ?int
     */
    public function getResultCount(): ?int
    {
        return $this->metaData->get('result_count');
    }

    /**
     * getNextToken
     *
     * @return ?string
     */
    public function getNextToken(): ?string
    {
        return $this->metaData->get('next_token');
    }
}

<?php

namespace App\Application\OutputData\InnerApiResponse;

/**
 * InnerApiResponse
 * ResponseData
 */
interface InnerApiResponse
{
    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * getBody
     *
     * @return void
     */
    public function getBody();

    /**
     * getBody
     *
     * @return array
     */
    public function getBodyAsArray(): array;

    /**
     * hasError
     *
     * @return bool
     */
    public function hasError(): bool;
}

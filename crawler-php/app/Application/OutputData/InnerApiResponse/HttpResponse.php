<?php

namespace App\Application\OutputData\InnerApiResponse;

/**
 * InnerApiResponse
 * ResponseData
 */
class HttpResponse implements InnerApiResponse
{
    public function __construct(
        private int $statusCode,
        private array|bool|int|float|string|object $body
    ) {
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * getBody
     *
     * @return void
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * getBody
     *
     * @return void
     */
    public function getBodyAsArray(): array
    {

        if (is_array($this->body) === true) {
            return $this->body;
        }

        if (($json = json_decode($this->body, true)) !== null) {
            return $json;
        }

        return [];
    }

    /**
     * hasError
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return !preg_match('/^2[0-9]{2}$/', $this->statusCode);
    }
}

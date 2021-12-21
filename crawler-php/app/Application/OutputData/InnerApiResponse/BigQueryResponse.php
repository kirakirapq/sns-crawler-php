<?php

namespace App\Application\OutputData\InnerApiResponse;

/**
 * InnerApiResponse
 * ResponseData
 */
class BigQueryResponse implements InnerApiResponse
{
    private int $statusCode;

    private $body;

    public function __construct(int $statusCode, $body)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
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
        return iterator_to_array($this->body->rows());
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

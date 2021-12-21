<?php

namespace App\Application\OutputData\InnerApiResponse;

/**
 * InnerApiResponse
 * ResponseData
 */
class CsvResponse implements InnerApiResponse
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
        return [$this->body];
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

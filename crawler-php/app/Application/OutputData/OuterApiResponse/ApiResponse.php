<?php

namespace App\Application\OutputData\OuterApiResponse;

class ApiResponse implements OuterApiResponse
{
    public function __construct(private array $message, private int $code)
    {
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * getMessage
     *
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }
}

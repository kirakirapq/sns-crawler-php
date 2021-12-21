<?php

namespace App\Application\OutputData\OuterApiResponse;

class ApiResponse implements OuterApiResponse
{
    private array $message;
    private int $code;

    public function __construct(array $message, int $code)
    {
        $this->message = $message;
        $this->code    = $code;
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

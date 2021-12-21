<?php

namespace App\Application\OutputData\OuterApiResponse;

interface OuterApiResponse
{
    public function getStatusCode(): int;

    public function getMessage(): array;
}

<?php

namespace App\Exceptions;

use \Exception;
use \Throwable;
use Illuminate\Support\Facades\Log;

class ObjectDefinitionErrorException extends Exception
{
    public function __construct($message, int $code = 0, array $parameters = [], Throwable $previous = null)
    {
        $msg = [
            'message' => $message,
            'parameters' => $parameters,
        ];

        Log::error(get_called_class(), $msg);

        parent::__construct($message, $code, $previous);
    }
}

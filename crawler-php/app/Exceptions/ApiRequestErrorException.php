<?php

namespace App\Exceptions;

use \Exception;
use \Throwable;
use Illuminate\Support\Facades\Log;

class ApiRequestErrorException extends Exception
{
    public function __construct(Exception $e, $message, $parameters = [], Throwable $previous = null)
    {
        $msg = [
            'message' => $message,
            'parameters' => $parameters,
        ];

        Log::error(get_called_class(), $msg);

        // 全てを正しく確実に代入する
        parent::__construct($e->getMessage(), $e->getCode(), $previous);
    }
}

<?php

namespace App\Exceptions;

use \Exception;
use \Throwable;

class BigQueryReqestErrorException extends ApiRequestErrorException
{
    public function __construct(Exception $e, $message, $parameters = [], Throwable $previous = null)
    {
        // 全てを正しく確実に代入する
        parent::__construct($e, $message, $parameters, $previous);
    }
}

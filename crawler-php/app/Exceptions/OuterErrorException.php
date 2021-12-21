<?php

namespace App\Exceptions;

use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use \Exception;
use \Throwable;
use Illuminate\Support\Facades\Log;

class OuterErrorException extends Exception
{
    public function __construct(ErrorDefinition $errorDefinition, $message)
    {
        $code = $this->getStatusCode($errorDefinition->getErrorCode());
        $message = [
            'Layer Code' => $errorDefinition->getLayerCode(),
            'Error Code' => $errorDefinition->getErrorCode(),
            'Error Message' => $message,
            'More information' => 'https://extra-confluence.gree-office.net/pages/viewpage.action?pageId=372821393',
        ];
        Log::error(get_called_class(), $message);

        parent::__construct(json_encode($message, JSON_UNESCAPED_SLASHES), $code);
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    protected function getStatusCode(?int $code = null): int
    {
        $statusCode = null;
        switch (true) {
            case preg_match('/^[2-5][0-9]{2}$/', $code):
                $statusCode = $code;
                break;
            default:
                $statusCode = 500;
        }

        return $statusCode;
    }
}

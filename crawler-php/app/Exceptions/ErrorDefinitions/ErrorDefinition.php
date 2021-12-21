<?php

namespace App\Exceptions\ErrorDefinitions;

use App\Exceptions\ObjectDefinitionErrorException;
use \ReflectionClass;

class ErrorDefinition
{
    private string $layerCode;
    private string $errorCode;

    /**
     * __construct
     *
     * @param  mixed $layerCode
     * @param  mixed $errorCode
     * @return void
     */
    public function __construct(string $layerCode, int $errorCode)
    {
        $reflectionClass = new ReflectionClass(LayerCode::class);
        if (in_array($layerCode, $reflectionClass->getConstants()) === false) {
            throw new ObjectDefinitionErrorException('Undifind Layer Code', 500);
        }

        $this->layerCode = $layerCode;
        $this->errorCode = $this->setErrorCode($layerCode, $errorCode);
    }

    /**
     * getLayerCode
     *
     * @return string
     */
    public function getLayerCode(): string
    {
        return $this->layerCode;
    }

    /**
     * getErrorCode
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * getErrorCode
     *
     * @param  mixed $layerCode
     * @param  mixed $errorCode
     * @return string
     */
    protected function setErrorCode(string $layerCode, int $errorCode): string
    {
        $code = '';
        switch ($layerCode) {
            case LayerCode::CONTROLL_LAYER_CODE:
                $code = sprintf('01_%s', $errorCode);
                break;
            case LayerCode::REPOSITORY_LAYER_CODE:
                $code = sprintf('02_%s', $errorCode);
                break;
            case LayerCode::USECASE_LAYER_CODE:
                $code = sprintf('03_%s', $errorCode);
                break;
            case LayerCode::MODEL_LAYER_CODE:
                $code = sprintf('04_%s', $errorCode);
                break;
        }

        return $code;
    }
}

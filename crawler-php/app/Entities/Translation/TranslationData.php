<?php

namespace App\Entities\Translation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class TranslationData
{
    private int $statusCode;

    private $text;

    private ?string $code;

    // private $errorMessage;

    // private bool $hasError;

    public function __construct(int $statusCode, Collection $collection)
    {
        $this->statusCode = $statusCode;
        $this->setData($collection);
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

    // /**
    //  * hasError
    //  *
    //  * @return bool
    //  */
    // public function hasError(): bool
    // {
    //     return $this->hasError;
    // }

    // /**
    //  * getErrorMessage
    //  *
    //  * @return void
    //  */
    // public function getErrorMessage()
    // {
    //     return $this->errorMessage ?? null;
    // }

    public function setData(Collection $collection)
    {
        foreach ($collection as $key => $value) {
            if (is_array($value) === true) {
                foreach ($value as  $v) {
                    $this->code = $resltData['code'] ?? '';
                    $this->text[] = $v;
                }
            } else {
                $this->text[] = $collection['text'];
                $this->code[] = $collection['code'] ?? '';
            }
        }
    }

    public function getCode(): ?string
    {
        return $this->code ?? null;
    }

    public function getText()
    {
        return $this->text ?? null;
    }
}

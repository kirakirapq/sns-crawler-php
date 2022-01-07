<?php

namespace App\Entities\Translation;

use Illuminate\Support\Collection;

final class TranslationData
{
    private int $statusCode;

    private ?array $text;

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

    public function setData(Collection $collection)
    {
        foreach ($collection as $translats) {
            foreach ($translats as $trans) {
                $this->text[] = $trans['text'] ?? '';
            }
        }
    }

    public function getText(): ?array
    {
        return $this->text ?? null;
    }
}

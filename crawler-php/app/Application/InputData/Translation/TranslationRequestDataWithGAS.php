<?php

namespace App\Application\InputData\Translation;

use App\Application\InputData\RequestType;

/**
 * 翻訳用API
 * Twitter 該当のuser_idのメンションがついたコメントを取得するためのリクエストデータ
 */
final class TranslationRequestDataWithGAS
{
    const BASE_URI = 'https://script.google.com/macros/s/%s/exec';
    const API_KEY = 'AKfycbykJY9MhTZkPKX3_cMB9U6RxF07l2PN-IbqfrBW0zBQMJWxiuvC';

    public function __construct(string $text, string $from, string $to = 'ja')
    {
        $this->text = $text;
        $this->from = $from;
        $this->to   = $to;
    }

    public function getUri(?string $text = null, ?string $from = null, ?string $to = null): string
    {
        $uri = sprintf(self::BASE_URI, self::API_KEY);

        return sprintf(
            '%s?text=%s&source=%s&target=%s',
            $uri,
            ($text ?? $this->text),
            ($from ?? $this->from),
            ($to ?? $this->to)
        );
    }

    public function getOptions(): array
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
            ]
        ];
    }

    public function getMethod(): string
    {
        return RequestType::GET;
    }
}

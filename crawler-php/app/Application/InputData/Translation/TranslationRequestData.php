<?php

namespace App\Application\InputData\Translation;

use App\Adapters\Translation\GoogleTranslationAdapter;
use Illuminate\Support\Facades\Config;

/**
 * 翻訳用API
 * Twitter 該当のuser_idのメンションがついたコメントを取得するためのリクエストデータ
 */
final class TranslationRequestData
{
    private string $location;
    private string $projectId;
    private array $textData;
    private string $version;
    private BCP47 $targetLanguageCode;
    private BCP47 $sourceLanguageCode;
    private array $option;

    public function __construct(array $textData, string $from, string $to = 'ja', string $version = 'V3')
    {
        $this->location = Config::get('app.GCP_TRANSLATION_LOCATION');
        $this->projectId = Config::get('app.GCP_PROJECT_ID');
        $this->version   = $version;
        $this->textData   = $textData;
        $this->targetLanguageCode = GoogleTranslationAdapter::getBCP47($to);
        $this->sourceLanguageCode = GoogleTranslationAdapter::getBCP47($from);
        $this->option = GoogleTranslationAdapter::getTranlationV3OptionalArray($this->projectId, $this->location, $this->sourceLanguageCode);
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getTextData(): array
    {
        return $this->textData;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getTargetLanguageCode(): string
    {
        return $this->targetLanguageCode->getCode();
    }

    public function getSourceLanguageCode(): string
    {
        return $this->sourceLanguageCode->getCode();
    }

    public function getOption(): array
    {
        return $this->option;
    }
}

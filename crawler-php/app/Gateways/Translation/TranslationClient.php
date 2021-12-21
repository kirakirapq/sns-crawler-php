<?php

namespace App\Gateways\Translation;

use App\Adapters\Translation\GoogleTranslationAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Application\InputData\Translation\TranslationRequestData;
use App\Application\InputData\Translation\TranslationRequestDataWithGAS;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\InputData\RequestType;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Entities\Translation\TranslationData;
use App\Entities\Translation\TranslationDataList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Translate\V2\TranslateClient;
use \Exception;

final class TranslationClient implements TranslationRepository
{
    const V2_SEGMENT_LIMIT = 100;
    const V2_BYTE_LIMIT = 100000;
    const V3_CODEPOINT_LIMIT = 30000;

    public function __construct(
        HttpClient $request
    ) {
        $this->request = $request;
    }

    /**
     * translation
     * Cloud Translationを使った翻訳
     * 30720 code(文字数？)の制限あり
     *
     * @return void
     */
    public function translation(
        TranslationRequestData $requestData
    ): TranslationData {
        $version = $requestData->getVersion();

        if ($version == 'V2') {
            $translate = new TranslateClient([
                'projectId' => $requestData->getProjectId(),
            ]);

            try {
                // @return [ [source, input, text], ... ]
                $response = $translate->translateBatch(
                    $requestData->getTextData(),
                    ['target' => $requestData->getTargetLanguageCode()]
                );
            } catch (Exception $e) {
                Log::error('TranslationClient::translation', [$e->getMessage()]);
                $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $e->getCode());

                throw new  OuterErrorException($ed, $e->getMessage());
            }

            return GoogleTranslationAdapter::getTranlationDataFromV2($response);
        }

        if ($version == 'V3') {
            $location = Config::get('app.GCP_TRANSLATION_LOCATION');

            $projectId = $requestData->getProjectId();
            $location = $requestData->getLocation();
            $contents = $requestData->getTextData();
            $targetLanguageCode = $requestData->getTargetLanguageCode();
            $optionalArgs = $requestData->getOption();


            $translationServiceClient = new TranslationServiceClient();
            try {
                $formattedParent = $translationServiceClient->locationName($projectId, $location);

                // @return Google\Cloud\Translate\V3\TranslateTextResponse
                $response = $translationServiceClient->translateText($contents, $targetLanguageCode, $formattedParent, $optionalArgs);
            } catch (Exception $e) {
                Log::error('TranslationClient::translation', [$e->getMessage()]);
                $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $e->getCode());

                throw new  OuterErrorException($ed, $e->getMessage());
            } finally {
                $translationServiceClient->close();
            }

            return GoogleTranslationAdapter::getTranlationDataFromV3($response);
        }
    }

    /**
     * translation
     * GASを使った翻訳 1日20000リクエストの制限あり
     *
     * @return void
     */
    public function translationWithGAS(
        TranslationRequestDataWithGAS $requestData
    ): TranslationData {
        $uri = $requestData->getUri();
        $options = $requestData->getOptions();

        $response = $this->request->get($uri, $options);

        if ($response->hasError() === false) {
            $translated = $response->getBodyAsArray();

            return new TranslationData(200, collect($translated));
        } else {
            Log::error('TranslationClient::translationWithGAS:', $response->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

            throw new  OuterErrorException($ed, $response->getBody());
        }
    }

    /**
     * translationlistWithGAS
     * GASを使った翻訳 1日20000リクエストの制限あり
     *
     * @param  mixed $apiCollection
     * @return TranslationDataList
     */
    public function translationlistWithGAS(Collection $apiCollection): TranslationDataList
    {
        $apiResponse = $this->request->requestAsync($apiCollection->pluck('url')->all(), RequestType::GET);

        if ($apiResponse->hasError() === false) {
            $translated = [];
            foreach ($apiResponse as $value) {
                $translated[]['text'] = $value->getBodyAsArray()['text'];
            }
        } else {
            Log::error('TranslationClient::translationWithGAS:', $apiResponse->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $apiResponse->getStatusCode());

            throw new  OuterErrorException($ed, $apiResponse->getBody());
        }

        return TranslationDataAdapter::getTranslationDataList($apiCollection, collect($translated));
    }

    /**
     * translationlist
     * google cloud translation sdkを使用したマルチ翻訳API
     * 30720 code(文字数？)の制限あり
     *
     * @param  mixed $apiCollection
     * @param  mixed $version
     * @return TranslationDataList
     */
    public function translationlist(
        Collection $apiCollection,
        string $version = 'V3'
    ): TranslationDataList {
        $projectId = Config::get('app.GCP_PROJECT_ID');

        if ($version == 'V2') {
            return $this->executeTranslationWithV2($projectId, $apiCollection, $version);
        }

        if ($version == 'V3') {
            return $this->executeTranslationWithV3($projectId, $apiCollection, $version);
        }
    }

    /**
     * executeTranslationWithV2
     * V2(Translation Basic)で実行
     *
     * @param  mixed $projectId
     * @param  mixed $apiCollection
     * @param  mixed $version
     * @return TranslationDataList
     */
    public function executeTranslationWithV2(
        string $projectId,
        Collection $apiCollection,
        string $version = 'V3'
    ): TranslationDataList {
        $language = $apiCollection->first()['language'];
        $target = GoogleTranslationAdapter::getBCP47($language['target']);
        $translate = new TranslateClient(['projectId' => $projectId,]);
        $chunk = [];
        try {
            foreach ($apiCollection as $i => $value) {
                $translateionTargetFieldName = $value['translateionTargetFieldName'];
                $chunk[] = $value[$translateionTargetFieldName];
                if ($this->isLimitOver($chunk, $version) === true) {
                    $popData = array_pop($chunk);
                    // @return [ [source, input, text], ... ]
                    $result = $translate->translateBatch($chunk, ['target' => $target->getCode()]);
                    $translated = GoogleTranslationAdapter::getTranlationDataListFromV2($apiCollection, $result);

                    $chunk = [];
                    $chunk[] = $popData;
                }

                if ($this->isLast($apiCollection, $i) === true) {
                    // @return [ [source, input, text], ... ]
                    $result = $translate->translateBatch($chunk, ['target' => $target->getCode()]);
                    $translated = GoogleTranslationAdapter::getTranlationDataListFromV2($apiCollection, $result);
                }
            }
        } catch (Exception $e) {
            Log::error('TranslationClient::executeTranslationWithV2', [$e->getMessage()]);
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $e->getCode());

            throw new  OuterErrorException($ed, $e->getMessage());
        }

        return $translated;
    }

    /**
     * executeTranslationWithV3
     * V3(Translation Advance)で実行
     *
     * @param  mixed $projectId
     * @param  mixed $apiCollection
     * @param  mixed $version
     * @return TranslationDataList
     */
    public function executeTranslationWithV3(
        string $projectId,
        Collection $apiCollection,
        string $version = 'V3'
    ): TranslationDataList {
        $location = Config::get('app.GCP_TRANSLATION_LOCATION');
        $language = $apiCollection->first()['language'];
        $source = GoogleTranslationAdapter::getBCP47($language['source']);
        $target = GoogleTranslationAdapter::getBCP47($language['target']);
        $optionalArgs = GoogleTranslationAdapter::getTranlationV3OptionalArray($projectId, $location, $source);
        $targetLanguageCode = $target->getCode();

        $translationServiceClient = new TranslationServiceClient();
        $formattedParent = $translationServiceClient->locationName($projectId, $location);
        try {
            $chunk = [];
            foreach ($apiCollection as $i => $value) {
                $translateionTargetFieldName = $value['translateionTargetFieldName'];
                $chunk[] = $value[$translateionTargetFieldName];
                if ($this->isLimitOver($chunk, $version) === true) {
                    $popData = array_pop($chunk);
                    // @return Google\Cloud\Translate\V3\TranslateTextResponse
                    $response = $translationServiceClient->translateText($chunk, $targetLanguageCode, $formattedParent, $optionalArgs);
                    $translated  = GoogleTranslationAdapter::getTranlationDataListFromV3($apiCollection, $response);

                    $chunk = [];
                    $chunk[] = $popData;
                }

                if ($this->isLast($apiCollection, $i) === true) {
                    // @return Google\Cloud\Translate\V3\TranslateTextResponse
                    $response = $translationServiceClient->translateText($chunk, $targetLanguageCode, $formattedParent, $optionalArgs);
                    $translated  = GoogleTranslationAdapter::getTranlationDataListFromV3($apiCollection, $response);
                }
            }
        } catch (Exception $e) {
            Log::error('TranslationClient::executeTranslationWithV3', [$e->getMessage()]);
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $e->getCode());

            throw new  OuterErrorException($ed, $e->getMessage());
        } finally {
            $translationServiceClient->close();
        }

        return $translated;
    }

    /**
     * isLast
     * Collectionの最後をチェック
     *
     * @param  mixed $collection
     * @param  mixed $index
     * @return bool
     */
    public function isLast(Collection $collection, $index): bool
    {
        if ($collection->count() === ($index + 1)) {
            return true;
        }

        return false;
    }

    /**
     * isLimitOver
     * サイズオーバーチェック
     *
     * @param  mixed $contents
     * @param  mixed $version
     * @return bool
     */
    public function isLimitOver(array $contents, string $version): bool
    {
        if ($version == 'V2') {
            return $this->isV2LimitOver($contents);
        }

        if ($version == 'V3') {
            return $this->isV3LimitOver($contents);
        }

        Log::error('TranslationClient::isLimitOver error', ['Undefind variable version.']);
        $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, 900);

        throw new  OuterErrorException($ed, 'TranslationClient::isLimitOver error');
    }

    /**
     * isV2LimitOver
     * サイズチェック
     * もしかしたら件数100件の制限があるかも？
     *
     * @param  mixed $contents
     * @return bool
     */
    public function isV2LimitOver(array $contents): bool
    {
        $size = strlen(implode('', $contents));
        if (self::V2_BYTE_LIMIT < $size) {
            Log::debug("TranslationClient::isV2LimitOver", ['size' => $size]);

            return true;
        }

        $count = count($contents);
        if (self::V2_SEGMENT_LIMIT < $count) {
            Log::debug("TranslationClient::isV2LimitOver", ['segment(count) limit' => $count]);

            return true;
        }

        return false;
    }

    /**
     * isV3LimitOver
     * The total codepoints in the request must be less than 30720,
     *
     * @param  mixed $contents
     * @return bool
     */
    public function isV3LimitOver(array $contents): bool
    {
        $size = mb_strlen(implode('', $contents));
        if (self::V3_CODEPOINT_LIMIT < $size) {
            Log::debug("TranslationClient::isV3LimitOver", ['Code points' => $size]);

            return true;
        }

        return false;
    }
}

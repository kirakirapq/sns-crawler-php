<?php

namespace App\Gateways;

use App\Adapters\TwitterApiAdapter;
use App\Application\InputData\TwitterApiRequestData;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\TwitterApiRepository;
use App\Entities\Twitter\TwitterMentionDataList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;

/**
 * TwitterApiRequest
 * TwitterRepositoryの実装クラス
 */
final class TwitterApiRequest implements TwitterApiRepository
{
    private TwitterApiRequestData $requestData;
    private HttpClient $request;

    public function __construct(
        TwitterApiRequestData $requestData,
        HttpClient $request
    ) {
        $this->requestData = $requestData;
        $this->request = $request;
    }

    /**
     * getMentions
     *
     * @return void
     */
    public function getMentions(
        string $userId,
        ?string $paginationToken = null
    ): TwitterMentionDataList {
        $uri = $this->requestData->getUri($userId, $paginationToken);
        $options = $this->requestData->getOptions();

        $response = $this->request->get($uri, $options);

        if ($response->hasError() === true) {
            Log::error('TwitterApiRequest::getMentions', $response->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

            throw new  OuterErrorException($ed, $response->getBody());
        }

        // エラーがなければボディの中身を取得してアダプターを介してドメインモデルを返却
        return TwitterApiAdapter::responseToMentionDataList($response->getBodyAsArray());
    }
}

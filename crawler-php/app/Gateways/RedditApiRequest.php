<?php

namespace App\Gateways;

use App\Adapters\RedditApiAdapter;
use App\Application\InputData\SubRedditRequestData;
use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\RedditApiRepository;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;

/**
 * RedditApiRequest
 */
class RedditApiRequest implements RedditApiRepository
{
    private HttpClient $httpClient;

    public function __construct(
        HttpClient $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * getSubReddit
     * subreddit内のthred一覧を取得する
     *
     * @param  SubRedditRequestData $requestData
     * @param  string $id
     * @return RedditDataList
     */
    public function getSubReddit(SubRedditRequestData $requestData, string $id): SubReddit
    {
        $uri = $requestData->getUri($id);
        $options = $requestData->getOptions();
        $response = $this->httpClient->get($uri, $options);

        if ($response->hasError() === true) {
            Log::error('RedditApiRequest::getSubReddit', $response->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

            throw new  OuterErrorException($ed, $response->getBodyAsArray());
        }
        // TODO: エラーがなければボディの中身を取得してアダプターを介してドメインモデルを返却
        return RedditApiAdapter::getSubReddit($response->getBodyAsArray());
    }

    /**
     * getSubReddit
     * subreddit内のthred一覧を取得する
     *
     * @param  SubRedditThreadCommentRequestData $requestData
     * @param  string $id
     * @return RedditDataList
     */
    public function getComment(SubRedditThreadCommentRequestData $requestData): Thread
    {
        $uri = $requestData->getUri();
        $options = $requestData->getOptions();
        $response = $this->httpClient->get($uri, $options);

        if ($response->hasError() === true) {
            Log::error('RedditApiRequest::getSubReddit', $response->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

            throw new  OuterErrorException($ed, $response->getBodyAsArray());
        }

        // TODO: エラーがなければボディの中身を取得してアダプターを介してドメインモデルを返却
        return RedditApiAdapter::getThread($response->getBodyAsArray(), $requestData);
    }
}

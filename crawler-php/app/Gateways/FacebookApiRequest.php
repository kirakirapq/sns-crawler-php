<?php

namespace App\Gateways;

use App\Adapters\FacebookApiAdapter;
use App\Application\InputData\Facebook\FacebookRequestData;
use App\Application\Repositories\FacebookApiRepository;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Entities\Facebook\FacebookDataList;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;

/**
 * TwitterApiRequest
 * TwitterRepositoryの実装クラス
 */
final class FacebookApiRequest implements FacebookApiRepository
{
    private HttpClient $request;

    public function __construct(
        HttpClient $request
    ) {
        $this->request = $request;
    }

    /**
     * getMentions
     *
     * @return void
     */
    public function getFacebookDataList(FacebookRequestData $reqestData): FacebookDataList
    {
        $uri = $reqestData->getUri();

        $response = $this->request->get($uri, []);

        if ($response->hasError() === true) {
            Log::error('FacebookApiRequest::getFacebookDataList', $response->getBodyAsArray());
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

            throw new  OuterErrorException($ed, $response->getBodyAsArray());
        }

        return FacebookApiAdapter::getFacebookDataList($reqestData->getReqestType(), $response->getBodyAsArray());
    }
}

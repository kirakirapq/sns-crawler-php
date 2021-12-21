<?php

namespace App\Gateways\Notification;

use App\Adapters\NotificationAdapter;
use App\Application\InputData\NotificationSendModel;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\Notification\NotificationClient;
use App\Entities\Notification\NotificationResponseModel;
use App\Exceptions\ErrorDefinitions\ErrorDefinition;
use App\Exceptions\ErrorDefinitions\LayerCode;
use App\Exceptions\OuterErrorException;
use Illuminate\Support\Facades\Log;

final class SlackClient implements NotificationClient
{
    private HttpClient $httpClient;

    public function __construct(
        HttpClient $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    public function notify(NotificationSendModel $notificationSendModel): NotificationResponseModel
    {
        $url = $notificationSendModel->getAddress();

        try {
            $response = $this->httpClient->postJson(
                $url,
                $notificationSendModel->getMessage(),
                ['headers' => ['Content-Type: application/json']]
            );

            if ($response->hasError() === true) {
                Log::error('SlackClient::notify', $response->getBodyAsArray());
                $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $response->getStatusCode());

                throw new  OuterErrorException($ed, $response->getBodyAsArray());
            }
        } catch (Exception $e) {
            Log::error('SlackClient::notify', [$e->getMessage()]);
            $ed = new ErrorDefinition(LayerCode::REPOSITORY_LAYER_CODE, $e->getCode());

            throw new  OuterErrorException($ed, $e->getMessage());
        }

        // エラーがなければボディの中身を取得してアダプターを介してドメインモデルを返却
        return NotificationAdapter::getNotificationResponseModel($response->getBodyAsArray());
    }
}

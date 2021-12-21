<?php

namespace App\Http\Controllers\Api;

use App\Adapters\RiskWordAdapter;
use App\Application\UseCases\Notification\NotificationInvoker;
// use App\Application\UseCases\Notification\NotificationUseCase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
// use PHPUnit\Framework\Constraint\FileExists;

class SlackNotificationController extends Controller
{
    private NotificationInvoker $notificationInvoker;

    public function __construct(
        NotificationInvoker $notificationInvoker
    ) {
        $this->notificationInvoker = $notificationInvoker;
    }

    /**
     * api spec:
     * endpoint: /api/notification/slack/riskword
     * method: post
     * request parameters example:
     * {
     *  "sns": "twitter",
     *  "messages": [
     *   {
     *       "app_name": "kms",
     *       "language": "en",
     *       "text": "text001........",
     *       "translated": "translatedxxxxxxxxxxxx",
     *         "created_at": "2021-01-01 00:00:00"
     *     },
     *     {
     *       "id": "id-002",
     *         "app_name": "kms",
     *         "language": "en",
     *         "text": "text002xxxxxxx",
     *         "translated": "translated2"
     *     }
     *   ]
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function notifyRiskWord(Request $request)
    {
        Config::set('crawl.name', $this->getCrawlName($request->all()['sns']));

        $riskCommentList = RiskWordAdapter::getRiskCommentListByRequestData($request);

        $response = $this->notificationInvoker->invokeNotifyRiskCommentList($riskCommentList);

        return response()->json(
            $response->getMessage(),
            $response->getStatusCode(),
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
        );
    }

    private function getCrawlName(string $sns): string
    {
        if (mb_strtolower($sns) === 'twitter') {
            return 'twitter';
        }

        if (mb_strtolower($sns) === 'reddit') {
            return 'reddit';
        }

        if (mb_strtolower($sns) === 'facebook') {
            return 'facebook';
        }

        if (mb_strtolower($sns) === 'navercafe') {
            return 'navercafe';
        }

        return '';
    }
}

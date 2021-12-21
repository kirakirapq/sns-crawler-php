<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\RiskWord\RiskWordBaseInvoker;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class RiskWordController extends Controller
{
    private RiskWordBaseInvoker $riskWordBaseInvoker;
    private RiskWordUseCase $riskWordUseCase;

    public function __construct(
        RiskWordBaseInvoker $riskWordBaseInvoker
    ) {
        $this->riskWordBaseInvoker = $riskWordBaseInvoker;
    }

    /**
     * api spec:
     * https://extra-confluence.gree-office.net/pages/viewpage.action?pageId=372821393
     *
     * @return \Illuminate\Http\Response
     */
    public function getRiskComment(string $sns, string $title, string $language, ?string $created_at = null)
    {
        Config::set('crawl.name', $this->getCrawlName($sns));

        $response = $this->riskWordBaseInvoker->invokeGetRiskComments(
            $title,
            $language,
            $created_at
        );

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

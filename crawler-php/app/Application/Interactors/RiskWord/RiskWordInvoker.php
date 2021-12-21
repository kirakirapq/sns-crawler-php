<?php

namespace App\Application\Interactors\RiskWord;

use App\Adapters\OuterApiResponseAdapter;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\UseCases\RiskWord\RiskWordBaseInvoker;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Exceptions\OuterErrorException;

class RiskWordInvoker implements RiskWordBaseInvoker
{
    private RiskWordUseCase $riskWordUsecase;

    public function __construct(RiskWordUseCase $riskWordUsecase)
    {
        $this->riskWordUsecase = $riskWordUsecase;
    }

    /**
     * invokeGetRiskComments
     *
     * @param  mixed $title
     * @param  mixed $language
     * @param  mixed $createdAt
     * @return OuterApiResponse
     */
    public function invokeGetRiskComments(
        string $title,
        string $language,
        ?string $createdAt = null
    ): OuterApiResponse {
        try {
            $result = $this->riskWordUsecase->getRiskComment($title, $language, $createdAt);

            if ($result->hasError() === true) {
                return OuterApiResponseAdapter::getFromArray([$result->getErrorMessage()], $result->getStatusCode());
            }

            return OuterApiResponseAdapter::getFromCollection($result->getCommentList(), $result->getStatusCode());
        } catch (OuterErrorException $e) {
            return OuterApiResponseAdapter::getFromOuterErrorException($e);
        }
    }
}

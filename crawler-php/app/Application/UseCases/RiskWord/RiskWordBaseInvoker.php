<?php

namespace App\Application\UseCases\RiskWord;

use App\Application\OutputData\OuterApiResponse\OuterApiResponse;

interface RiskWordBaseInvoker
{
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
    ): OuterApiResponse;
}

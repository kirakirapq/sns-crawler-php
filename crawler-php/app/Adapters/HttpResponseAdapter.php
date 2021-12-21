<?php

namespace App\Adapters;

use App\Application\OutputData\InnerApiResponse\HttpResponse;

final class HttpResponseAdapter
{
    /**
     * translationMentionDataList
     *
     * @param  mixed $httpResponse
     * @return void
     */
    static public function guzzleResponseToHttpResponse($response): HttpResponse
    {
        return new HttpResponse($response->getStatusCode(), $response->getBody()->getContents());
    }
}

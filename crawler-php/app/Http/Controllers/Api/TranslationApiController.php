<?php

namespace App\Http\Controllers\Api;

use App\Adapters\OuterApiResponseAdapter;
use App\Adapters\TranslationDataAdapter;
use App\Http\Controllers\Controller;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\UseCases\Translation\TranslationInvoker;
use App\Exceptions\OuterErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TranslationApiController extends Controller
{
    private TranslationInvoker $translationInvoker;

    public function __construct(
        TranslationInvoker $translationInvoker
    ) {
        $this->translationInvoker = $translationInvoker;
    }

    /**
     * api spec:
     * endpoint: /api/translation
     * method: post
     * request parameter:
     * contents.[] : transla text
     * language.from : from language
     * language.to : to language
     * example: language format is "ISO 639-1"
     * {"contents": ["string", "string"], "language": {"from":"en", "to": "ja"}}
     *
     * @return \Illuminate\Http\Response
     */
     public function translation(Request $request)
     {
         try {
             Log::info('TranslationApiController::translation', ['job start']);
             $version = 'V3';
             $requestData = TranslationDataAdapter::getTranslationRequestData($request->all(), $version);

             $response = $this->translationInvoker->invokeTranslation($requestData);
             Log::info('TranslationApiController::translation', ['job complete']);
         } catch (OuterErrorException $e) {
             $response = OuterApiResponseAdapter::getFromOuterErrorException($e);
         }

         return response()->json(
             $response->getMessage(),
             $response->getStatusCode(),
             ['Content-Type' => 'application/json'],
             JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
         );
     }
}

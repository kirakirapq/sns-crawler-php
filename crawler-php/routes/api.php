<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['api']], function () {
    // SNSクローリングユースケースを通しで行うAPI
    Route::get(
        '/crawl/sns/{sns}/title/{title}/lunguage/{lunguage}',
        "App\Http\Controllers\Api\SnsCrawlController@crawling"
    );

    Route::group(['middleware' => ['sns-comment']], function () {
        Route::get(
            '/sns/{sns}/title/{title}/date/{date}',
            "App\Http\Controllers\Api\SnsCommentApiController@getAll"
        );
        Route::get(
            '/sns/{sns}/title/{title}/from/{from}/to/{?to}',
            "App\Http\Controllers\Api\SnsCommentApiController@getFrom"
        );
        Route::get(
            '/sns/{sns}/title/{title}/date/{date}/where/{field}/op/{op}/value/{value}',
            "App\Http\Controllers\Api\SnsCommentApiController@getWhere"
        );
    });

    // 翻訳API
    Route::post(
        '/translation',
        "App\Http\Controllers\Api\TranslationApiController@translation"
    );

    // リスクワードを通知するAPI
    Route::post(
        '/notification/slack/riskword',
        "App\Http\Controllers\Api\SlackNotificationController@notifyRiskWord"
    );

    // リスクワードを取得するAPI
    Route::get(
        '/riskword/comments/sns/{sns}/title/{title}/lunguage/{lunguage}/created_at/{created_at?}',
        "App\Http\Controllers\Api\RiskWordController@getRiskComment"
    );
});

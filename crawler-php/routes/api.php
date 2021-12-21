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
    Route::get(
        '/crawl/sns/{sns}/title/{title}/lunguage/{lunguage}',
        "App\Http\Controllers\Api\SnsCrawlController@crawling"
    );

    Route::post(
        '/translation',
        "App\Http\Controllers\Api\TranslationApiController@translation"
    );

    Route::post(
        '/notification/slack/riskword',
        "App\Http\Controllers\Api\SlackNotificationController@notifyRiskWord"
    );

    Route::get(
        '/notification/slack/test',
        "App\Http\Controllers\Api\SlackNotificationController@notifyTest"
    );

    Route::get(
        '/riskword/comments/sns/{sns}/title/{title}/lunguage/{lunguage}/created_at/{created_at?}',
        "App\Http\Controllers\Api\RiskWordController@getRiskComment"
    );
});

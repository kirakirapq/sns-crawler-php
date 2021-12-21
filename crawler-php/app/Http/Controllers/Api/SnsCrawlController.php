<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Facebook\FacebookCrawlerUseCase;
use App\Application\UseCases\Reddit\RedditCrawlerUseCase;
use App\Application\UseCases\SnsCrawlUseCase;
use App\Application\UseCases\Twitter\TwitterCrawlerUseCase;
use App\Http\Controllers\Controller;

class SnsCrawlController extends Controller
{
    private FacebookCrawlerUseCase $facebookCrawlerUseCase;
    private TwitterCrawlerUseCase $twitterCrawlerUseCase;
    private RedditCrawlerUseCase $redditCrawlerUseCase;

    public function __construct(
        FacebookCrawlerUseCase $facebookCrawlerUseCase,
        TwitterCrawlerUseCase $twitterCrawlerUseCase,
        RedditCrawlerUseCase $redditCrawlerUseCase
    ) {
        $this->facebookCrawlerUseCase = $facebookCrawlerUseCase;
        $this->twitterCrawlerUseCase = $twitterCrawlerUseCase;
        $this->redditCrawlerUseCase = $redditCrawlerUseCase;
    }
    /**
     * api spec:
     * endpoint: /api/crawl/sns/{sns}/title/{title}/lunguage/{lunguage}
     * method: get
     *
     * @return \Illuminate\Http\Response
     */
    public function crawling(string $sns, string $title, string $language)
    {
        // ユースケースを実行
        $useCase = $this->getUseCase($sns);
        $response = $useCase->invokeCrawling($sns, $title, $language);

        return response()->json(
            $response->getMessage(),
            $response->getStatusCode(),
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
        );
    }

    /**
     * getUseCase
     *
     * @param  mixed $sns
     * @param  mixed $title
     * @param  mixed $language
     * @return SnsCrawlUseCase
     */
    public function getUseCase(string $sns): SnsCrawlUseCase
    {
        switch ($sns) {
            case SnsCrawlerType::TWITTER:
                $useCase = $this->twitterCrawlerUseCase;
                break;
            case SnsCrawlerType::FB:
                $useCase = $this->facebookCrawlerUseCase;
                break;
            case SnsCrawlerType::REDDIT:
                $useCase = $this->redditCrawlerUseCase;
                break;
            case SnsCrawlerType::TEST:
                $useCase = $this->twitterCrawlerUseCase;
                break;
            default:
                $useCase = $this->twitterCrawlerUseCase;
                break;
        }
        return $useCase;
    }
}

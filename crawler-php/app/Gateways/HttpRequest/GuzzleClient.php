<?php

namespace App\Gateways\HttpRequest;

use App\Adapters\HttpResponseAdapter;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\OutputData\InnerApiResponse\HttpResponse;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Exceptions\HttpRequestErrorException;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * GuzzleClient
 * HttpClientの実装クラス
 */
final class GuzzleClient implements HttpClient
{
    public function __construct()
    {
        $this->client = new Client();
    }

    public function get(string $uri, array $options): InnerApiResponse
    {
        try {
            Log::info('GuzzleRequest::get');
            $response = $this->client->get($uri, $options);

            return HttpResponseAdapter::guzzleResponseToHttpResponse($response);
        } catch (\Exception $e) {
            throw new HttpRequestErrorException(
                $e,
                'GuzzleRequest::get',
                [
                    'uri' => $uri,
                    'options' => $options
                ]
            );
        }
    }

    public function create(): InnerApiResponse
    {
        return new HttpResponse(201, '');
    }

    public function update(): InnerApiResponse
    {
        return new HttpResponse(201, '');
    }

    public function delete(): InnerApiResponse
    {
        return new HttpResponse(204, '');
    }

    /**
     * requestAsync
     *
     * @param  mixed $urls
     * @param  mixed $method
     * @return Collection
     */
    public function requestAsync(array $urls, string $method): Collection
    {
        $requests = function ($urls, $method) {
            foreach ($urls as $index => $url) {
                yield $index => function () use ($url, $method) {
                    // return  new Request($method, $url);
                    return $this->client->requestAsync($method, $url);
                };
            }
        };

        $contents = [];

        $pool = new Pool($this->client, $requests($urls, $method), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($urls, &$contents) {
                $contents[$index] = HttpResponseAdapter::guzzleResponseToHttpResponse($response);
            },
            'rejected' => function ($reason, $index) use ($urls, &$contents) {
                // this is delivered each failed request
                $contents[$index] = new HttpResponse(0, $reason);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return collect($contents);
    }

    public function post(string $url, array $data, array $options = []): InnerApiResponse
    {
        try {
            $options['form_params'] = $data;

            Log::info('GuzzleRequest::postJson');
            $response = $this->client->post($url, $options);

            return HttpResponseAdapter::guzzleResponseToHttpResponse($response);
        } catch (\Exception $e) {
            throw new HttpRequestErrorException(
                $e,
                'GuzzleRequest::postJson',
                [
                    'uri' => $url,
                    'options' => $options
                ]
            );
        }
    }

    public function postJson(string $url, array $data, array $options = []): InnerApiResponse
    {
        try {
            $options['json'] = $data;

            Log::info('GuzzleRequest::postJson');
            $response = $this->client->post($url, $options);

            return HttpResponseAdapter::guzzleResponseToHttpResponse($response);
        } catch (\Exception $e) {
            throw new HttpRequestErrorException(
                $e,
                'GuzzleRequest::postJson',
                [
                    'uri' => $url,
                    'options' => $options
                ]
            );
        }
    }
}

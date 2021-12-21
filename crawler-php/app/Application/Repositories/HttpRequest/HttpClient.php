<?php

namespace App\Application\Repositories\HttpRequest;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use Illuminate\Support\Collection;

interface HttpClient
{
    public function get(string $uri, array $options): InnerApiResponse;

    public function create(): InnerApiResponse;

    public function update(): InnerApiResponse;

    public function delete(): InnerApiResponse;

    public function requestAsync(array $urls, string $method): Collection;
}

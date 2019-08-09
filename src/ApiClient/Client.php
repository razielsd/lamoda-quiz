<?php

declare(strict_types=1);

namespace App\ApiClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;



class Client
{
    /**
     * @var string
     */
    protected $host = 'webserver';


    protected $httpClient = null;


    /**
     * Get container(s)
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContainer(int $limit, int $offset = 0): array
    {
        $url = $this->createUrl('/container');
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];
        try {
            $resp = $this->getHttpClient()->request(
                'GET',
                $url,
                ['query' => $params]
            );
        } catch (RequestException $e) {
            throw new \Exception('Error due get containers:' . $e->getMessage(), 0, $e);
        }
        $body = $resp->getBody()->getContents();
        return json_decode($body, true);
    }


    /**
     * Put container
     *
     * @param array $container
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeContainer(array $container): int
    {
        $url = $this->createUrl('/container');
        $rawData = json_encode($container);
        try {
            $resp = $this->getHttpClient()->request(
                'POST',
                $url,
                ['body' => $rawData]
            );
        } catch (RequestException $e) {
            throw new \Exception('Error due put container:' . $e->getMessage(), 0, $e);
        }
        $body = $resp->getBody()->getContents();
        $resp = json_decode($body, true);
        return $resp['id'];
    }


    /**
     * @param string $uri
     *
     * @return string
     */
    protected function createUrl(string $uri): string
    {
        return \sprintf('http://%s%s', $this->host, $uri);
    }


    /**
     * @return HttpClient
     */
    protected function getHttpClient(): HttpClient
    {
        if ($this->httpClient === null) {
            $this->httpClient = $this->createHttpClient();
        }
        return $this->httpClient;
    }


    /**
     * @return HttpClient
     */
    protected function createHttpClient(): HttpClient
    {
        $client = new HttpClient([
            // Base URI is used with relative requests
            'base_uri' => 'localhost',
            // You can set any number of default request options.
            'timeout' => 20.0,
        ]);

        return $client;
    }

}
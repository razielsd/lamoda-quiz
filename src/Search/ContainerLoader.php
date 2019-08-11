<?php

declare(strict_types=1);

namespace App\Search;


use App\ApiClient\Client;


class ContainerLoader
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $maxContainers = 1000;

    /**
     * @var int
     */
    protected $containerCounter = 0;

    /**
     * @var int
     */
    protected $chunkSize = 20;


    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Загружаем контейнеры из сервиса
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(): array
    {
        if ($this->containerCounter >= $this->maxContainers) {
            return [];
        }
        $containerList = $this->client->getContainerSlice($this->chunkSize, $this->containerCounter);
        $this->containerCounter += count($containerList);
        return $containerList;
    }

    /**
     * @return int
     */
    public function getMaxContainers(): int
    {
        return $this->maxContainers;
    }

    /**
     * @param int $maxContainers
     * @return ContainerLoader
     */
    public function setMaxContainers(int $maxContainers): ContainerLoader
    {
        $this->maxContainers = $maxContainers;
        return $this;
    }

    /**
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     * @return ContainerLoader
     */
    public function setChunkSize(int $chunkSize): ContainerLoader
    {
        $this->chunkSize = $chunkSize;
        return $this;
    }

}
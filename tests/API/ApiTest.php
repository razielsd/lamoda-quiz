<?php

use PHPUnit\Framework\TestCase;
use \App\ApiClient\Client;


class ApiTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client = null;


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testStoreContainer()
    {
        $container = $this->createContainer();
        $id = $this->getClient()->storeContainer($container);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetByIdContainer()
    {
        $container = $this->createContainer();
        $id = $this->getClient()->storeContainer($container);
        $this->assertGreaterThan(0, $id);

        $stored = $this->getClient()->getContainerById($id);
        $this->assertArrayHasKey($id, $stored);
        $this->assertEquals($container['title'], $stored[$id]['title']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSliceContainer()
    {
        $size = 5;
        $this->fillStorage($size);
        $loaded = $this->getClient()->getContainerSlice($size);
        $prev = -1;
        foreach ($loaded as $id => $row) {
            $this->assertGreaterThan($prev, $id, 'Next id must be greater previous');
            $this->assertEquals($id, $row['id'], 'index must be equal id');
            $prev = $id;
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSliceOffsetContainer()
    {
        $limit = 10;
        $offset = 5;
        $this->fillStorage($limit + $offset);

        $fromBegin = $this->getClient()->getContainerSlice($limit, 0);
        $fromOffset = $this->getClient()->getContainerSlice($limit, $offset);
        $this->assertEquals($limit, count($fromBegin), 'Bad count containers from start');
        $this->assertEquals($limit, count($fromOffset), 'Bad count containers from offset');
        $beginIds = array_keys(array_slice($fromBegin, $offset, $limit - $offset, true));
        $offsetIds = array_keys(array_slice($fromOffset, 0, $limit - $offset, true));

        $this->assertEquals(
            join(', ', $offsetIds),
            join(', ', $beginIds),
            'Bad work offset'
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLoadContainerNotFound()
    {
        //не совсем идеологически верно  хардкодить число ... надо сначала убедиться, что нет такого
        $loaded = $this->getClient()->getContainerSlice(1, 10000000);
        $this->assertEmpty($loaded);
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }

    /**
     * @return array
     */
    protected function createContainer(): array
    {
        $ids = range(1, 100);
        shuffle($ids);
        $items = [];
        for ($i = 0;$i < 10;$i++) {
            $items[] = [
                'id' => $ids[$i],
                'title' => 'item-' . $ids[$i]
            ];
        }
        return ['title' => 'Container ' . mt_rand(1000, 9999), 'items' => $items];
    }

    /**
     * @param int $count
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function fillStorage(int $count): array
    {
        $storage = [];
        for ($i = 0;$i < $count;$i++) {
            $container = $this->createContainer();
            $id = $this->getClient()->storeContainer($container);
            $this->assertGreaterThan(0, $id);
            $this->assertArrayNotHasKey($id, $storage, 'Error getting container, duplicate');
            $storage[$id] = $container;
        }
        return $storage;
    }
}
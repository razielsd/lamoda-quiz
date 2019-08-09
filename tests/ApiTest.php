<?php

require_once dirname(__DIR__, 1) . '/config/bootstrap.php';

use PHPUnit\Framework\TestCase;
use \App\ApiClient\Client;


class ApiTest extends TestCase
{
    protected $client = null;

    public function testStoreContainer()
    {
        $container = $this->createContainer();
        $id = $this->getClient()->storeContainer($container);
        $this->assertGreaterThan(0, $id);
    }


    public function testLoadContainer()
    {
        $size = 5;
        //если у нас гарантированно не пустая база - заполнять не обязательно, но ... я иногда базу очищаю
        $this->fillStorage($size);
        $loaded = $this->getClient()->getContainer($size);
        $prev = -1;
        foreach ($loaded as $id => $row) {
            $this->assertGreaterThan($prev, $id, 'Next id must be greater previous');
            $this->assertEquals($id, $row['id'], 'index must be equal id');
            $prev = $id;
        }
    }


    public function testLoadOffsetContainer()
    {
        $size = 15;
        $this->fillStorage($size);
        $limit = 10;
        $offset =$size - $limit;
        $fromBegin = $this->getClient()->getContainer($limit + $offset, 0);
        $fromOffset = $this->getClient()->getContainer($limit, $offset);
        $this->assertEquals($limit + $offset, count($fromBegin), 'Bad count containers from start');
        $this->assertEquals($limit, count($fromOffset), 'Bad count containers from offset');
        $this->assertEquals(
            join(', ', array_keys($fromOffset)),
            join(', ', array_keys(array_slice($fromBegin, $offset, $limit, true))),
            'Bad work offset'
        );
    }


    public function testLoadContainerNotFound()
    {
        //не совсем идеологически верно  хардкодить число ... надо сначала убедиться, что нет такого
        $loaded = $this->getClient()->getContainer(1, 10000000);
        $this->assertEmpty($loaded);
    }


    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }


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
<?php

use PHPUnit\Framework\TestCase;
use \App\Search\ContainerList;
use \App\Search\Container;

class ContainerListTest extends TestCase
{
    public function testIntersection_Empty()
    {
        $containerList = new ContainerList();
        $container = $this->createContainer(1);
        $this->assertEmpty($containerList->getIntersection($container), 'Intersection cannot be found');
    }

    public function testIntersection_FoundAll()
    {
        $containerList = new ContainerList();
        $idList = [1, 2, 3, 5, 10];
        $container = $this->createContainer(1, $idList);
        $containerList->add($container);
        $this->assertCount(
            count($idList),
            $containerList->getIntersection($container),
            'Must be found all items'
        );
    }

    public function testIntersection_FoundPartial()
    {
        $containerList = new ContainerList();
        $idList = [1, 2, 3, 5, 10];
        $container = $this->createContainer(1, $idList);
        $containerList->add($container);

        $idList = [12, 2, 4, 7, 50];
        $container = $this->createContainer(1, $idList);
        $containerList->add($container);

        $idList = [11, 2, 3, 7, 80];
        $container = $this->createContainer(2, $idList);

        $this->assertCount(
            3,
            $containerList->getIntersection($container),
            'Must be found specified items'
        );
    }

    /**
     * @param $id
     * @param array $itemIdList
     * @return Container
     */
    protected function createContainer($id, $itemIdList = []): Container
    {
        if (empty($itemIdList)) {
            $itemIdList = range(1, 100);
            shuffle($itemIdList);
        }
        $items = [];
        for ($i = 0;$i < count($itemIdList);$i++) {
            $items[] = [
                'id' => $itemIdList[$i],
                'title' => 'item-' . $itemIdList[$i]
            ];
        }
        return new Container([
            'id' => $id,
            'title' => 'Container ' . mt_rand(1000, 9999),
            'items' => $items
        ]);
    }

}
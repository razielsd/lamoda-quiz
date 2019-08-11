<?php

declare(strict_types=1);

namespace App\Search;

class ContainerList implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $containerList = [];
    /**
     * @var int
     */
    protected $cursor = 0;
    /**
     * @var array
     */
    protected $map = [];


    /**
     * @param Container $container
     * @return ContainerList
     */
    public function add(Container $container): self
    {
        $this->containerList[$container->getId()] = $container;
        foreach ($container->getItemIdList() as $itemId) {
            $this->map[$itemId] = isset($this->map[$itemId]) ? ($this->map[$itemId] + 1) : 1;
        }
        return $this;
    }

    /**
     * @param int $id
     * @return ContainerList
     */
    public function remove(int $id): self
    {
        if (!isset($this->containerList[$id])) {
            /** @var Container $container */
            $container = $this->containerList[$id];
            foreach ($container->getItemIdList() as $itemId) {
                --$this->map[$itemId];
            }
            $this->map = array_filter($this->map);
            unset($this->containerList[$id]);
        }
        return $this;
    }

    /**
     * @param Container $container
     * @return array
     */
    public function getIntersection(Container $container): array
    {
        $ids = $container->getItemIdList();
        sort($ids, SORT_NUMERIC);
        $result = [];
        foreach ($ids as $itemId) {
            if (isset($this->map[$itemId])) {
                $result[] = $itemId;
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getUniqIdCount(): int
    {
        return count($this->map);
    }

    /**
     * @return array
     */
    public function getUniqIdMap(): array
    {
        return $this->map;
    }


    public function rewind()
    {
        reset($this->containerList);
    }

    public function current()
    {
        $var = current($this->containerList);
        return $var;
    }

    public function key()
    {
        $var = key($this->containerList);
        return $var;
    }

    public function next()
    {
        $var = next($this->containerList);
        return $var;
    }

    public function valid()
    {
        $key = key($this->containerList);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->containerList);
    }

}
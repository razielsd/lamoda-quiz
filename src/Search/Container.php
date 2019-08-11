<?php

declare(strict_types=1);

namespace App\Search;

class Container
{
    protected $id;
    protected $title;
    protected $itemList = [];


    /**
     * Container constructor.
     * @param array $container
     */
    public function __construct(array $container)
    {
        $this->id = (int) $container['id'];
        $this->title = $container['title'];
        $this->itemList = $container['items'];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->itemList;
    }

    /**
     * @return array
     */
    public function getItemIdList(): array
    {
        return array_column($this->itemList, 'id');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $data = [
            'id' => $this->id,
            'items' => []
        ];
        foreach ($this->itemList as $item) {
            $data['items'][] = $item['id'];
        }
        sort($data['items'], SORT_NUMERIC);
        return json_encode($data);
    }
}
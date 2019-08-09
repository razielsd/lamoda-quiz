<?php

namespace App\Search;

class Container
{
    protected $id;
    protected $title;
    protected $itemList = [];


    public function __construct(array $container)
    {
        $this->id = $container['id'];
        $this->title = $container['title'];
        $this->itemList = $container['items'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItems(): array
    {
        return $this->itemList;
    }


    public function getItemIdList(): array
    {
        return array_column($this->itemList, 'id');
    }
}
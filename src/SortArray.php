<?php

declare(strict_types=1);

namespace DocsDeploy;

class SortArray
{
    private array $module;

    private array $order = [];

    private array $array = [];

    public function __construct(array $module, array $order = [])
    {
        $this->module = $module;
        $this->order = $order;
        $knownKeys = array_keys($this->module);
        $availableKeys = array_intersect($this->order, $knownKeys);
        $this->array = array_merge(array_flip($availableKeys), $this->module);
    }

    public function toArray(): array
    {
        return $this->array;
    }
}

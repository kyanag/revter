<?php

namespace Kyanag\Revter\Libs\DataStorages;

class JsonlDataStorage
{

    protected $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function insert($type, $item)
    {
        $file = $this->dir . DIRECTORY_SEPARATOR . "{$type}.jsonl";
        file_put_contents($file, json_encode($item, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }

    public function insertAll($type, $items)
    {
        foreach ($items as $item) {
            $this->insert($type, $item);
        }
    }
}
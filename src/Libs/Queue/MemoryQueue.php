<?php

namespace Kyanag\Revter\Libs\Queue;

use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;

class MemoryQueue implements ReadonlyQueueInterface
{
    protected $items = [];


    protected $ttl = 5;


    public function __construct(array $urls = [], $ttl = 5)
    {
        $this->ttl = $ttl;
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
    }


    public function dequeue()
    {
        return array_pop($this->items);
    }


    public function addUrl(string $url, string $method = 'GET', array $headers = [], $body = null): array
    {
        return $this->items[] = [
            'request' => [
                'method' => $method,
                'url' => $url,
                'headers' => $headers,
                'body' => $body
            ],
            'ttl' => $this->ttl,
            'vars' => []
        ];
    }
}
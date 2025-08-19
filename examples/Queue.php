<?php
namespace Examples;

use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;

class Queue implements ReadonlyQueueInterface
{

    protected $items = [];


    public function __construct(array $urls = [])
    {
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
    }


    public function dequeue()
    {
        return array_pop($this->items);
    }


    public function addUrl(string $url, string $method = 'GET')
    {
        $this->items[] = [
            'method' => $method,
            'url' => $url,
            'headers' => [],
            'body' => null
        ];
    }
}
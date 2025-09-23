<?php

namespace Kyanag\Revter\Core;

use Kyanag\Revter\Core\Interfaces\HandlerInterface;
use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;

/**
 *  @template T
 */
final class Runner
{
    /**
     * @var HandlerInterface<T>
     */
    protected $executor;


    /**
     * @var \Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface<T>
     */
    protected $queue;


    /**
     * @param HandlerInterface<T> $executor
     * @param ReadonlyQueueInterface<T> $queue
     */
    public function __construct(HandlerInterface $executor, ReadonlyQueueInterface $queue)
    {
        $this->executor = $executor;
        $this->queue = $queue;
    }


    public function run()
    {
        while (true) {
            $task = $this->queue->dequeue();
            if ($task === null) {
                break;
            }
            $this->executor->handle($task);
        }
    }
}
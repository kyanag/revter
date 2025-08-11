<?php

namespace Kyanag\Revter\Core\Interfaces;

/**
 * @template T
 */
interface ReadonlyQueueInterface
{


    /**
     * @return ?T
     */
    public function dequeue();


}
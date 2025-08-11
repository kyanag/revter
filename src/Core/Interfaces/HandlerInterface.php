<?php

namespace Kyanag\Revter\Core\Interfaces;

/**
 * @template T
 */
interface HandlerInterface
{

    /**
     * @param T $task
     * @return mixed
     */
    public function handle($task);

}
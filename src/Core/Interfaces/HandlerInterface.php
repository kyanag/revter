<?php

namespace Kyanag\Revter\Core\Interfaces;

/**
 * @template T
 */
interface HandlerInterface
{

    /**
     * @param T $work
     * @return mixed
     */
    public function handle($work);

}
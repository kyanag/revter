<?php

namespace Kyanag\Revter\Core\Interfaces;

/**
 * @template T
 */
interface HandlerInterface
{

    /**
     * @param T $request
     * @return mixed
     */
    public function handle($request);

}
<?php

namespace Kyanag\Revter;

trait MiddlewareAbleTrait
{

    protected $middlewares = [];


    /**
     * @param ...$middlewares
     * @return self
     */
    public function middleware(...$middlewares): self
    {
        $middlewares = array_reverse($middlewares);
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }


    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
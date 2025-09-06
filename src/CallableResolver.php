<?php

namespace Kyanag\Revter;

use Kyanag\Revter\Core\Interfaces\HandlerInterface;
use SplObjectStorage;

class CallableResolver
{

    /**
     * @var object?
     */
    protected $owner = null;


    public function __construct($owner = null)
    {
        $this->owner = $owner;
    }


    public function setOwner($owner)
    {
        $this->owner = $owner;
    }


    public function resolve($callable): callable
    {
        if(!is_object($this->owner)) {
            return $callable;
        }
        if($callable instanceof \Closure) {
            $callable = $callable->bindTo($this->owner);
        }else if(is_object($callable) && method_exists($callable, 'setOwner')) {
            $callable->setOwner($this->owner);
        }
        return $callable;
    }


    public function createMiddlewareAbleInvoker(callable $invoker, $middlewares = []): callable
    {
        $invoker = $this->resolve($invoker);
        foreach ($middlewares as $middleware) {
            $middleware = $this->resolveMiddleware($middleware);
            $next = $invoker;

            $invoker = function (...$args) use ($middleware, $next) {
                $args[] = $next;
                return call_user_func($middleware, ...$args);
            };
        }
        return $invoker;
    }


    public function resolveMiddleware($middleware): callable
    {
        return $this->resolve($middleware);
    }
}
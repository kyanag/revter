<?php

namespace Kyanag\Revter;

use Psr\Http\Message\ServerRequestInterface;

class Route implements \ArrayAccess
{
    /**
     * @var string|array
     */
    protected $method;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var callable
     */
    protected $handler;


    /** @var string|mixed|null  */
    protected $id = "";


    protected $middlewares = [];


    public function __construct($method, $pattern, $handler, $id = null)
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->id = $id;
    }


    public function name($id)
    {
        $this->id = $id;
    }


    public function middlewares($middlewares = [])
    {
        $this->middlewares = $middlewares;
    }


    public function handle(ServerRequestInterface $request, $var = [])
    {
        return call_user_func_array($this->handler, [$request, $var]);
    }


    /**
     * @param object $object
     * @return bool
     */
    public function setOwner($object)
    {
        if($this->handler instanceof \Closure && $object){
            $this->handler = $this->handler->bindTo($object);
            return true;
        }
        return false;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'method' => $this->method,
            'pattern' => $this->pattern,
            'handler' => $this->handler,
            'id' => $this->id,
        ];
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException("Property offset is immutable.");
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Property offset is immutable.");
    }
}
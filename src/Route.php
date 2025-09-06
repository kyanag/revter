<?php

namespace Kyanag\Revter;

use Psr\Http\Message\ServerRequestInterface;

class Route
{

    use MiddlewareAbleTrait;

    /**
     * @var string|array
     */
    protected $method;

    /**
     * @var string
     */
    protected $pattern;


    /** @var string|null  */
    protected $name = "";


    protected $handler;


    /**
     * @param callable $handler
     * @param $method
     * @param string $pattern
     * @param string|null $name
     */
    public function __construct($method, string $pattern, callable $handler, string $name = null)
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;

        $this->name = $name;
    }


    public function name($id)
    {
        $this->name = $id;
    }

    public function getMethods(): array
    {
        return (array)$this->method;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getName()
    {
        return $this->name;
    }
}
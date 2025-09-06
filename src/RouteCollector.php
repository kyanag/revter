<?php

namespace Kyanag\Revter;

use FastRoute\Dispatcher;
use Kyanag\Revter\Route;
use function FastRoute\simpleDispatcher;

class RouteCollector
{
    /**
     * @var string
     */
    protected $prefix = "/";

    /**
     * @var array
     */
    protected $routes = [];

    /**
     *
     * @var ?callable
     */
    protected $requestRewrite;


    public function addRoute($method, $pattern, $handler, $id = null)
    {
        $route = $this->createRoute($method, $pattern, $handler, $id);
        $this->routes[] = $route;
        return $route;
    }


    public function on($pattern, $handler, $method = "GET", $id = null)
    {
        $this->addRoute("GET", $pattern, $handler, $id);
    }


    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the past callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup($prefix, callable $callback)
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $this->mergePrefix($this->prefix, $prefix);
        $callback($this);
        $this->prefix = $previousPrefix;
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function get($pattern, $handler)
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function post($pattern, $handler)
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function put($pattern, $handler)
    {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function delete($pattern, $handler)
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function patch($pattern, $handler)
    {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function head($pattern, $handler)
    {
        return $this->addRoute('HEAD', $pattern, $handler);
    }


    protected function mergePrefix($currentPrefix, $prefix)
    {
        return "/" . implode("/", array_filter([
                rtrim($currentPrefix, "/"),
                ltrim($prefix, "/"),
            ]));
    }

    protected function createRoute($method, $pattern, $handler, $as = null)
    {
        if ($pattern == "*") {
            if ($as === null) {
                $as = "/" . implode("/", array_filter([
                        trim($this->prefix, "/"),
                        "*",
                    ]));
                $as = "404@({$as})";
            }
            $pattern = "{routes:.*}";
        }
        $pattern = "/" . implode("/", array_filter([
                trim($this->prefix, "/"),
                ltrim($pattern, "/"),
            ]));
        $as = $as ?: "";
        return new Route(
            $method,
            $pattern,
            $handler,
            $as
        );
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * @return Dispatcher
     */
    public function toFastrouteDispatcher()
    {
        return simpleDispatcher(function (\FastRoute\RouteCollector $collector) {
            $routes = $this->getRoutes();
            /** @var Route $route */
            foreach ($routes as $route) {
                $collector->addRoute($route['method'], $route['pattern'], $route);
            }
            return $collector;
        });
    }
}
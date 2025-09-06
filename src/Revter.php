<?php

namespace Kyanag\Revter;

use ArrayObject;
use FastRoute\Dispatcher;
use Kyanag\Revter\Core\Interfaces\HandlerInterface;
use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;
use Kyanag\Revter\Core\Runner;
use Psr\Http\Message\RequestInterface;
use function FastRoute\simpleDispatcher;

/**
 * <code>
 *     
 * </code>
 */
abstract class Revter implements HandlerInterface
{

    use MiddlewareAbleTrait;

    /**
     * @var callable|null
     */
    protected $requestRewrite = null;


    /**
     * 路由收集器
     * @var RouteCollector
     */
    protected $routeCollector;


    /**
     * @var CallableResolver
     */
    protected $callableResolver;


    /** @var callable */
    protected $invoker;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;


    public function __construct()
    {
        $this->callableResolver = new CallableResolver($this);

        $this->routeCollector = new RouteCollector();

        $this->invoker = [$this, "handleRequest"];
    }


    public function useOwner($owner)
    {
        $this->callableResolver->setOwner($owner);
    }


    /**
     * @param callable $requestRewrite
     * @return void
     */
    public function setRequestRewrite(callable $requestRewrite)
    {
        $this->requestRewrite = $requestRewrite;
    }


    /**
     * @param string $pattern
     * @param callable|HandlerInterface<RequestInterface, array> $callable
     * @param string $method
     * @return Route
     */
    public function on(string $pattern, $callable, string $method = "GET")
    {
        return $this->routeCollector->addRoute($method, $pattern, $callable);
    }


    public function group($prefix, callable $callback)
    {
        $this->routeCollector->addGroup($prefix, $callback);
    }


    /**
     * @param  $work
     */
    public function handle($work)
    {
        try{
            $this->trigger("work.start", [
                'work' => $work
            ]);
            $request = $this->createPsrRequest($work);
            $res = call_user_func($this->invoker, $request);

            $this->trigger("work.success", [
                'work' => $work,
                'request' => $request,
                'res' => $res,
            ]);
            return $res;
        }catch (\Throwable $exception){
            $this->handleException($work, $exception);

            $this->trigger("work.failed", [
                'work' => $work,
                'request' => $request ?? null,
                'res' => $res ?? null,
                'exception' => $exception,
            ]);
        } finally {
            $this->trigger("work.end", [
                'work' => $work,
                'request' => $request ?? null,
                'res' => $res ?? null,
                'exception' => $exception ?? null,
            ]);
        }
    }

    /**
     * @param RequestInterface $request
     * @return mixed|null
     * @throws RouteUnmatchedException
     * @throws \Throwable
     */
    public function handleRequest(RequestInterface $request)
    {
        $this->trigger("request.start", [
            'request' => $request,
        ]);
        try{
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();
            //有重写规 则使用重写规则
            if ($this->requestRewrite) {
                list($method, $path) = call_user_func($this->requestRewrite, $request);
            }
            $route_result = $this->dispatcher->dispatch($method, $path);

            $vars = [
                '__dispatch_vars' => [
                    'method' => $method,
                    'path' => $path,
                ],
            ];
            switch ($route_result[0]) {
                case Dispatcher::FOUND:
                    $route = $route_result[1];

                    $vars = array_replace($vars, [
                        '__route' => $route,
                    ], $route_result[2]);
                    $res = $this->invokeRoute($route, $request, $vars);

                    $this->trigger("request.success", [
                        'request' => $request,
                        'vars' => $vars,
                        'res' => $res,
                    ]);

                    return $res;
                default:
                    throw new RouteUnmatchedException($request, $vars);
            }
        } catch (\Throwable $exception){
            $this->trigger("request.failed", [
                'request' => $request,
                'res' => $res ?? null,
                'exception' => $exception,
            ]);
            throw $exception;
        }finally {
            $this->trigger("request.end", [
                'request' => $request,
                'vars' => $vars ?? null,
                'res' => $res ?? null,
            ]);
        }
    }


    public function createRouteDispatcher()
    {
        $routes = $this->routeCollector->getRoutes();
        $routes = array_map(function(Route $route){
            $invoker = $route->getHandler();
            $middlewares = $route->getMiddlewares();
            $invoker = $this->callableResolver->createMiddlewareAbleInvoker($invoker, $middlewares);

            //合并 Middleware 后，生成新的 Route
            return new Route(
                $route->getMethods(),
                $route->getPattern(),
                $invoker,
                $route->getName()
            );
        }, $routes);

        return simpleDispatcher(function (\FastRoute\RouteCollector $fast) use($routes){
            /** @var Route $route */
            foreach ($routes as $route){
                $fast->addRoute($route->getMethods(), $route->getPattern(), $route);
            }
        });
    }


    public function run(ReadonlyQueueInterface $queue)
    {
        $handler = $this->createWorkHandler();
        (new Runner($handler, $queue))->run();
    }


    protected function invokeRoute(Route $route, $request, $vars)
    {
        $handler = $route->getHandler();
        return call_user_func_array($handler, [$request, $vars]);
    }


    /**
     * @return HandlerInterface
     */
    protected function createWorkHandler()
    {
        $this->dispatcher = $this->createRouteDispatcher();
        $this->invoker = $this->callableResolver->createMiddlewareAbleInvoker([$this, "handleRequest"], $this->middlewares);

        return $this;
    }


    /**
     * 从队列任务中创建 psr7-RequestInterface
     * @param $work
     * @return RequestInterface
     */
    abstract public function createPsrRequest($work);


    /**
     * 异常处理
     * @param $work
     * @param \Throwable $exception
     * @return mixed
     */
    abstract public function handleException($work, \Throwable $exception);


    /**
     * 事件处理
     * @param $name
     * @param $args
     * @return mixed
     */
    abstract public function trigger($name, $args = []);
}
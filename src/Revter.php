<?php

namespace Kyanag\Revter;

use FastRoute\Dispatcher;
use Kyanag\Revter\Core\Interfaces\HandlerInterface;
use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;
use Kyanag\Revter\Core\Runner;
use Psr\Http\Message\RequestInterface;

/**
 * @implements HandlerInterface<RequestInterface>
 */
abstract class Revter implements HandlerInterface
{

    /**
     * @var callable|null
     */
    protected $requestRewrite = null;

    /**
     * 路由 Dispatcher
     * @var Dispatcher
     */
    protected $dispatcher = null;


    /**
     * 路由收集器
     * @var RouteCollector
     */
    protected $routeCollector;


    public function __construct()
    {
        $this->routeCollector = new RouteCollector();
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
    public function on(string $pattern, $callable, string $method = "GET"): Route
    {
        return $this->routeCollector->addRoute($method, $pattern, $callable);
    }


    public function group($prefix, callable $callback)
    {
        $this->routeCollector->addGroup($prefix, $callback);
    }

    /**
     * 队列出队数据应符合以下规则
     * <code>
     *   struct RequestTaskInfo {
     *      'request': {
     *          'method': "GET",
     *          'url': "",
     *          'headers': [],
     *          'body': "mixed"
     *      },
     *      'ttl': 5,
     *      'vars': [],
     *  }
     *  ReadonlyQueueInterface<RequestTaskInfo>
     * </code>
     * @param ReadonlyQueueInterface $queue
     * @return void
     */
    public function run(ReadonlyQueueInterface $queue)
    {
        $this->dispatcher = $this->routeCollector->toFastrouteDispatcher();
        (new Runner($this, $queue))->run();
    }


    /**
     * @param RequestInterface $request
     * @return mixed|null
     */
    public function handle($request)
    {
        try{
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();
            //有重写规 则使用重写规则
            if ($this->requestRewrite) {
                list($method, $path) = call_user_func($this->requestRewrite, $request);
            }
            $route_result = $this->dispatcher->dispatch($method, $path);
            switch ($route_result[0]) {
                case Dispatcher::FOUND:
                    $handler = $route_result[1];

                    $vars = array_replace([
                        '@dispatch_var@' => [
                            'method' => $method,
                            'path' => $path,
                        ],
                    ], $route_result[2]);
                    return $handler->handle($request, $vars);
                default:
                    throw new RouteUnmatchedException();
            }
        }catch (\Exception $e){
            $this->handleException($e, $request, [$method, $path]);
        }
        return null;
    }

    /**
     * 触发事件
     * @param string $name
     * @param object $eventArgs
     * @return mixed
     */
    abstract public function trigger(string $name, $eventArgs);


    /**
     * 异常处理
     * @param \Throwable $e
     * @param RequestInterface $request
     * @param mixed $content
     * @return mixed
     */
    abstract protected function handleException(\Throwable $e, $request, $content = []);

}
<?php

namespace Kyanag\Revter;

use FastRoute\Dispatcher;
use Kyanag\Revter\Core\Interfaces\HandlerInterface;
use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;
use Kyanag\Revter\Core\Runner;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @implements HandlerInterface<array>
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
     * @param callable|HandlerInterface<ServerRequestInterface, array> $callable
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


    public function handle($request)
    {
        try{
            $method = $request['method'];
            $path = parse_url($request['url'], PHP_URL_PATH);
            //有重写规 则使用重写规则
            if ($this->requestRewrite) {
                list($method, $path) = call_user_func($this->requestRewrite, $request);
            }
            $route_result = $this->dispatcher->dispatch($method, $path);
            switch ($route_result[0]) {
                case Dispatcher::FOUND:
                    $psr_request = $this->createPsr7Request($request, [
                        'method' => $method,
                        'path' => $path,
                    ]);

                    $handler = $route_result[1];
                    $vars = $route_result[2];
                    return $handler->handle($psr_request, $vars);
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
     * @param mixed $request
     * @param mixed $content
     * @return mixed
     */
    abstract protected function handleException(\Throwable $e, $request, $content = []);


    /**
     * 根据任务生成 RequestInterface
     * @param $request
     * @param array $dispatch_vars  用于路由的参数
     * @return RequestInterface
     */
    abstract protected function createPsr7Request($request, array $dispatch_vars): RequestInterface;
}
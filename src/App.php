<?php

namespace Kyanag\Revter;

use ArrayObject;
use DI\Container;
use GuzzleHttp\Psr7\ServerRequest;
use Kyanag\Revter\Core\Revter;
use Kyanag\Revter\Core\RouteUnmatchedException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * <code>
 *     $task = struct RequestTaskInfo {
 *        'request': {
 *            'method': "GET",
 *            'url': "",
 *            'headers': [],
 *            'body': "mixed"
 *        },
 *        'ttl': 5,
 *        'vars': [],
 *    }
 *  </code>
 *
 * @property LoggerInterface $logger
 */
class App extends Revter
{


    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;


    protected $container;


    public function __construct()
    {
        parent::__construct();

        $this->eventDispatcher = new EventDispatcher();

        $this->container = new Container();
    }


    public function use(callable $callback)
    {
        call_user_func($callback, $this);
    }


    public function __set($name, $value)
    {
        $this->container->set($name, $value);
    }


    public function __get($name)
    {
        return $this->container->get($name);
    }


    public function __call($name, $arguments)
    {
        if($this->container->has($name)) {
            return $this->container->call($name, $arguments);
        }
        throw new \Exception("Call to undefined method " . __CLASS__ . "::" . $name . "()");
    }


    public function listen($name, $listener)
    {
        if(!is_null($listener)) {
            $listener = $this->callableResolver->resolve($listener);
        }
        if($name instanceof EventSubscriberInterface) {
            $this->eventDispatcher->addSubscriber($name);
        }else if(is_string($name) && is_callable($listener)) {
            $this->eventDispatcher->addListener($name, $listener);
        }
    }


    public function createPsrRequest($work)
    {
        $request = $work['request'];
        if(is_string($request)) {
            $serverRequest = new ServerRequest("GET", $request);
        }else{
            $serverRequest = new ServerRequest($request['method'], $request['url'], $request['headers'], $request['body']);
        }
        return $serverRequest
            ->withAttribute("_ttl", $work['ttl'])
            ->withAttribute("_vars", $work['vars']);
    }

    public function handleException($work, \Throwable $exception)
    {
        if($exception instanceof RouteUnmatchedException){
            $vars = $exception->getVars();
            $request = $exception->getRequest();

            echo "\t {$request->getMethod()} {$request->getUri()}}\n";
        }
        echo "\t" . $exception->getMessage() . "\n";
        echo "\t{$exception->getFile()}[Line:{$exception->getLine()}]" . "\n";
        echo "\t";
        echo str_replace("\n", "\n\t", $exception->getTraceAsString());

        $work['ttl'] -= 1;
        if($work['ttl'] >= 0){
            $this->trigger("work.abandoned", [
                'work' => $work,
            ]);
        }else{
            $this->trigger("work.retry", [
                'work' => $work,
            ]);
        }
    }

    public function trigger($name, $args = [])
    {
        $args = new ArrayObject($args);
        $this->eventDispatcher->dispatch($args, $name);
    }
}
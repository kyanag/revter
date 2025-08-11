<?php
namespace Examples;

use GuzzleHttp\Psr7\ServerRequest;
use Kyanag\Revter\Revter;

class ExampleRevter extends Revter
{

    public function __construct()
    {
        parent::__construct();

    }

    public function trigger(string $name, $eventArgs)
    {
        echo "{$name}\n";
    }

    /**
     * @param Exception $e
     * @param array $task
     * @param mixed $content
     * @return void
     */
    protected function handleException($e, $task, $content = [])
    {
        echo "失败: {$task['request']['method']}:{$task['request']['url']}\n";
        echo "Exception: [" . get_class($e) . "] {$e->getMessage()}\n";
        echo "\n\n";
    }


    protected function createPsr7Request($task, array $dispatch_vars): \Psr\Http\Message\RequestInterface
    {
        $request = $task['request'];

        $psr_request = new ServerRequest(
            $request['method'],
            $request['url'],
            $request['headers'],
            $request['body']
        );
        return $psr_request
            ->withAttribute("_task", $task)
            ->withAttribute("_dispatch_vars", $dispatch_vars);

    }
}
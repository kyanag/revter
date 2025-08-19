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
     * @param \Throwable $e
     * @param array $request
     * @param mixed $content
     * @return void
     */
    protected function handleException(\Throwable $e, $request, $content = [])
    {
        echo "失败: {$request['method']}:{$request['url']}\n";
        echo "Exception: [" . get_class($e) . "] {$e->getMessage()}\n";
        echo "\n\n";
    }


    protected function createPsr7Request($request, array $dispatch_vars): \Psr\Http\Message\RequestInterface
    {
        $psr_request = new ServerRequest(
            $request['method'],
            $request['url'],
            $request['headers'],
            $request['body']
        );
        return $psr_request
            ->withAttribute("_task", $request)
            ->withAttribute("_dispatch_vars", $dispatch_vars);

    }
}
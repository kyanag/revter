<?php
namespace Examples;

use GuzzleHttp\Psr7\ServerRequest;
use Kyanag\Revter\Revter;

class ExampleApp extends Revter
{


    public function createPsrRequest($work)
    {
        $psr_request = new ServerRequest(
            $work['method'],
            $work['url'],
            $work['headers'],
            $work['body']
        );
        return $psr_request
            ->withAttribute("__work", $work);
    }


    public function trigger($name, $args = [])
    {
        $titles = [
            "request.start" => "[event]开始",
            "request.success" => "[event]成功",
            "request.failed" => "[event]失败",
        ];

        if(isset($titles[$name])){
            $title = $titles[$name];
            switch ($name){
//                case "request.start":
//                    echo "{$title}: {$args['request']->getMethod()} {$args['request']->getUri()}\n";
//                    break;
//                case "request.success":
//                    echo "{$title}: {$args['request']->getMethod()} {$args['request']->getUri()}\n";
//
//                    unset($args['vars']['__route']);
//                    $render = var_export($args['vars'], true);
//                    $render = str_replace("\n", "\n\t", $render);
//                    echo "\t{$render}\n\n";
//                    break;
                case "request.failed":
                    $request = $args['request'];
                    $exception = $args['exception'];

                    echo "{$title}: {$request->getMethod()} {$request->getUri()}\n";
                    echo "\tException: [" . get_class($exception) . "] {$exception->getMessage()}\n";
                    echo "\n\n";
                    break;

            }
        }
    }

    public function handleException($work, \Throwable $exception)
    {
        // TODO: Implement handleException() method.
    }
}
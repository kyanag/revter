<?php

use Kyanag\Revter\Factory;
use Kyanag\Revter\Libs\Html\Html;

include __DIR__ . "/../vendor/autoload.php";

//设置运行目录
$path = setcwd(runtime_path("zhihu"));

$app = new \Kyanag\Revter\App();

$storage = Factory::makeDataStorage("jsonl", [
    'dir' => getcwd(),
]);

$client = new GuzzleHttp\Client([
    'base_uri' => 'https://www.zhihu.com/',
    'timeout'  => 5.0,
    'verify' => false,
    //'proxy' => "socks://127.0.0.1:10808",
]);

$logger = Factory::makeLogger("zhihu", "app.log", true);


//添加延迟
$app->addMiddleware(function ($request, $next) use ($logger) {
    try {
        $res = $next($request);
        sleep(1);
        return $res;
    }catch (\Exception $e){
        return null;
    }
});

//全局中间件
$app->addMiddleware(function (\Psr\Http\Message\ServerRequestInterface $request, $next) use($client, $logger){
    $logger->info("任务 {$request->getMethod()}:{$request->getUri()} 开始");
    $logger->info("[App::Middleware@before] {$request->getMethod()}:{$request->getUri()}");

// 发送请求并注入到 request 中
//    $response = $client->send($request);
//    $request = $request->withAttribute("response", $response);
    $res = $next($request);
    $logger->info("[App::Middleware@after] {$request->getMethod()}:{$request->getUri()}\n\n");
    return $res;
});

$app->on("/question/{question_id}", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []) use($logger){
    //问题贴页面
    unset($vars['__route'], $vars['__dispatch_vars']);
    $res = str_replace("\n", "\n\t", var_export($vars, true));

    $logger->info(implode("", [
        "{$request->getMethod()}:{$request->getUri()}\n",
        "\tquestion_id = {$vars['question_id']}\n",
        "\tvars = {$res}"
    ]));
});

//支持前缀路由组
$app->group("/people/{uid}", function(\Kyanag\Revter\Core\RouteCollector $collector) use($logger){
    // => "/people/{uid}/answers"
    $collector->on("/answers", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []) use($logger){
        //用户主页 - 回答列表
        unset($vars['__route'], $vars['__dispatch_vars']);

        $res = str_replace("\n", "\n\t", var_export($vars, true));
        $logger->info(implode("", [
            "{$request->getMethod()}:{$request->getUri()}\n",
            "\tuid = {$vars['uid']}\n",
            "\tvars = {$res}"
        ]));
    });
});

$app->on("/collection/{collection_id}", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []) use($logger){
    //收藏夹页
    unset($vars['__route'], $vars['__dispatch_vars']);

    $res = str_replace("\n", "\n\t", var_export($vars, true));

    $logger->info(implode("", [
        "{$request->getMethod()}:{$request->getUri()}\n",
        "\tcollection_id = {$vars['collection_id']}\n",
        "\tvars = {$res}"
    ]));
})->addMiddleware(function ($request, $vars, $next) use($logger){
    //路由中间件
    $logger->info("[Route::Middleware@before] collection_id = {$vars['collection_id']}");
    $res = call_user_func($next, $request, $vars);
    $logger->info("[Route::Middleware@after] collection_id = {$vars['collection_id']}");
    return $res;
});

/** @var \Kyanag\Revter\Libs\Queue\MemoryQueue $queue */
$queue = Factory::makeQueue("memory");

$queue->addUrl('https://www.zhihu.com/question/1936165649241076667');
$queue->addUrl('https://zhuanlan.zhihu.com/p/676908347');
$queue->addUrl('https://www.zhihu.com/collection/459061635');

$app->run($queue);

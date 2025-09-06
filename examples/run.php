<?php

include("../vendor/autoload.php");
include("./Queue.php");
include("./ExampleApp.php");

//创建一个 实现自 Kyanag\Router\Core\Interfaces\ReadonlyQueueInterface 的队列
$queue = new \Examples\Queue([
    'https://www.zhihu.com/question/1936165649241076667',
    'https://zhuanlan.zhihu.com/p/676908347',
    'https://www.zhihu.com/collection/459061635'
]);

//创建一个 继承于 Kyanag\Router\Router 的 Router
$revter = new \Examples\ExampleApp();


/**
 * 路由重定向
 * 可以用于 discuz 此类：对同一资源的多种 url 进行归一化处理
 */
$revter->setRequestRewrite(function(\Psr\Http\Message\ServerRequestInterface $request){
    return ["GET", $request->getUri()->getPath()];
});

//添加路由
$revter->on("/question/{question_id}", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []){
    //问题贴页面
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tquestion_id = {$vars['question_id']}\n";

    unset($vars['__route']);
    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
}, "GET");

//支持前缀路由组
$revter->group("/people/{uid}", function(\Kyanag\Revter\RouteCollector $collector){
    // => "/people/{uid}/answers"
    $collector->on("/answers", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []){
        //用户主页 - 回答列表
        echo "{$request->getMethod()}:{$request->getUri()}\n";
        echo "\tuid = {$vars['uid']}\n";

        unset($vars['__route']);
        $res = str_replace("\n", "\n\t", var_export($vars, true));
        echo "\tvars = {$res}\n";
    });
});

$revter->on("/collection/{collection_id}", function(\Psr\Http\Message\ServerRequestInterface $request, $vars = []){
    //收藏夹页
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tcollection_id = {$vars['collection_id']}\n";

    unset($vars['__route']);
    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
})->middleware(function ($request, $vars, $next){
    //路由中间件: 此时已经路由完毕

    echo "[RouteMiddleware:before] collection_id = {$vars['collection_id']}\n";
    $res = call_user_func($next, $request, $vars);
    echo "[RouteMiddleware:after] collection_id = {$vars['collection_id']}\n";
    return $res;
});

$revter->middleware(function ($request, $next){
    //全局中间件 - 尚未到路由阶段
    echo "[Middleware:before] \n";
    $res = call_user_func($next, $request);
    echo "[Middleware:after]\n\n\n";
    return $res;
});
$revter->run($queue);

$output = <<<OUTPUT
GET:https://www.zhihu.com/collection/459061635
        collection_id = 459061635
        vars = \ArrayObject::__set_state(array(
           '__dispatch_vars' =>
          array (
            'method' => 'GET',
            'path' => '/collection/459061635',
          ),
           'collection_id' => '459061635',
        ))


[event]失败: GET https://zhuanlan.zhihu.com/p/676908347
        Exception: [Kyanag\Revter\RouteUnmatchedException] 没有匹配的路由


GET:https://www.zhihu.com/question/1936165649241076667
        question_id = 1936165649241076667
        vars = \ArrayObject::__set_state(array(
           '__dispatch_vars' =>
          array (
            'method' => 'GET',
            'path' => '/question/1936165649241076667',
          ),
           'question_id' => '1936165649241076667',
        ))

OUTPUT;

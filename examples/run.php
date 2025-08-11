<?php

include("../vendor/autoload.php");
include("./Queue.php");
include("./ExampleRevter.php");

//创建一个 实现自 Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface 的队列
$queue = new \Examples\Queue([
    'https://www.zhihu.com/question/1936165649241076667',
    'https://zhuanlan.zhihu.com/p/676908347',
    'https://www.zhihu.com/collection/459061635'
]);

//创建一个 继承于 Kyanag\Revter\Revter 的 Revter
$revter = new \Examples\ExampleRevter();

//添加路由
$revter->on("/question/{question_id}", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
    //问题贴页面
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tquestion_id = {$vars['question_id']}\n";

    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
    echo "\n\n";
}, "GET");

//支持前缀路由组
$revter->group("/people/{uid}", function(\Kyanag\Revter\RouteCollector $collector){
    // => "/people/{uid}/answers"
    $collector->on("/answers", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
        //用户主页 - 回答列表
        echo "{$request->getMethod()}:{$request->getUri()}\n";
        echo "\tuid = {$vars['uid']}\n";

        $res = str_replace("\n", "\n\t", var_export($vars, true));
        echo "\tvars = {$res}\n";
        echo "\n\n";
    });
});

$revter->on("/collection/{collection_id}", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
    //收藏夹页
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tcollection_id = {$vars['collection_id']}\n";

    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
    echo "\n\n";
});
$revter->run($queue);

$output = <<<OUTPUT
GET:https://www.zhihu.com/collection/459061635
        collection_id = 459061635
        vars = array (
          'collection_id' => '459061635',
        )


失败: GET:https://zhuanlan.zhihu.com/p/676908347
Exception: [Kyanag\Revter\RouteUnmatchedException] 没有匹配的路由


GET:https://www.zhihu.com/question/1936165649241076667
        question_id = 1936165649241076667
        vars = array (
          'question_id' => '1936165649241076667',
        )
OUTPUT;

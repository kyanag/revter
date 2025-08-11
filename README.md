# 反方向的路由

**Rev**erseRou**ter**，用写 Web 的方式写爬虫

# 注意⚠️

这里只是一个对 nikic/fast-route 的一个封装 + 基于队列驱动的任务循环

甚至都不包括发送 Http 请求

要实现完整的爬虫程序，请自行在 [Revter](./src/Revter.php) 类的基础上实现其他内容




### 代码见 [run.php](./examples/run.php)
```php
//创建一个 实现自 Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface 的队列
$queue = new \Examples\Queue([
    'https://www.zhihu.com/question/1936165649241076667',
    'https://zhuanlan.zhihu.com/p/676908347',
    'https://www.zhihu.com/collection/459061635'
]);

//创建一个 继承于 Kyanag\Revter\Revter 的 Revter
$revter = new \Examples\ExampleRevter();

//添加路由 - 问题/贴子
$revter->on("/question/{question_id}", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tquestion_id = {$vars['question_id']}\n";

    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
    echo "\n\n";
}, "GET");  //支持其他 Http Method

//支持前缀路由组 - 用户主页
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

////添加路由 - 专栏文章页
//$revter->on("/p/{article_id}", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
//    //收藏夹页
//    echo "{$request->getMethod()}:{$request->getUri()}\n";
//    echo "\tarticle_id = {$vars['article_id']}\n";
//
//    $res = str_replace("\n", "\n\t", var_export($vars, true));
//    echo "\tvars = {$res}\n";
//    echo "\n\n";
//});

//添加路由 - 收藏夹
$revter->on("/collection/{collection_id}", function(\Psr\Http\Message\ServerRequestInterface $request, array $vars = []){
    //收藏夹页
    echo "{$request->getMethod()}:{$request->getUri()}\n";
    echo "\tcollection_id = {$vars['collection_id']}\n";

    $res = str_replace("\n", "\n\t", var_export($vars, true));
    echo "\tvars = {$res}\n";
    echo "\n\n";
});

//挂载到队列，并开始运行，队列为空(即 dequeue() 返回 null)后停止
$revter->run($queue);
```

输出如下
```txt
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
```
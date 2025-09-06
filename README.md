# 反方向的路由

**Rev**erseRou**ter**，用写 Web 的方式写爬虫

# 特点

1. 用类似 Slim / Laravel 此类路由风格的方式写爬虫处理代码
2. 支持路由中间件
3. 支持全局中间件
4. ⚠️除了 路由 和 PSR7 依赖，需自行实现 日志处理 / 事件处理 / 队列 / Http请求 等内容⚠️

## Vars 参数详情

1. __dispatch_vars


### 代码见 [run.php](./examples/run.php)

```php
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
 * 可以用于 discuz! 此类， 同一资源拥有多种 url 形式 进行转换，归一化处理
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
    //支持路由中间件: 此时已经路由完毕

    echo "[RouteMiddleware:before] collection_id = {$vars['collection_id']}\n";
    $res = call_user_func($next, $request, $vars);
    echo "[RouteMiddleware:after] collection_id = {$vars['collection_id']}\n";
    return $res;
});

$revter->middleware(function ($request, $next){
    //支持全局中间件: 此时尚未到路由阶段
    echo "[Middleware:before] \n";
    $res = call_user_func($next, $request);
    echo "[Middleware:after]\n\n\n";
    return $res;
});
$revter->run($queue);
```

输出如下
```txt
[Middleware:before] 
[RouteMiddleware:before] collection_id = 459061635
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
[RouteMiddleware:after] collection_id = 459061635
[Middleware:after]


[Middleware:before]
[event]失败: GET https://zhuanlan.zhihu.com/p/676908347
        Exception: [Kyanag\Revter\RouteUnmatchedException] 没有匹配的路由


[Middleware:before]
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
[Middleware:after]

```
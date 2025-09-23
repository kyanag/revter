<?php


/**
 * 设置工作目录
 * @param string $app
 * @return string
 * @throws Exception
 */
function setcwd(string $path)
{
    if(!file_exists($path)){
        mkdir($path, 775, true);
    }
    $path = realpath($path);
    if(chdir($path) === false){
        throw new \Exception("切换目录失败，请确认有无权限！[{$path}]");
    }
    return $path;
}


function runtime_path(string $app = "app"): string
{
    return __DIR__ . "/runtimes/{$app}";
}


if(!function_exists("value_get"))
{
    /**
     * @param object|array|\ArrayAccess $target
     * @param $key
     * @param $default
     * @return mixed|null
     */
    function value_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $keys = is_array($key) ? $key : explode('.', $key);
        foreach ($keys as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return $default;
                }
                $target = $target[$segment];
            } elseif ($target instanceof \ArrayAccess) {
                if (!isset($target[$segment])) {
                    return $default;
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->$segment)) {
                    return $default;
                }
                $target = $target->$segment;
            } else {
                return $default;
            }
        }
        return $target;
    }
}


function resolve_real_url($base_url, $relative_url) {
    $baseParts = parse_url($base_url);
    $relativeParts = parse_url($relative_url);
    if (isset($relativeParts['scheme'])) {
        return $relative_url; // 已经是绝对 URL
    }
    $path = $baseParts['path'] ?? '/';
    if (str_starts_with($relative_url, '/')) {
        $path = $relative_url;
    } else {
        $path = dirname($path) . '/' . $relative_url;
    }
    // 标准化路径（替换 ../ 或 ./）
    $path = preg_replace('~/\./~', '/', $path); // 替换 /./
    $path = preg_replace('~/[^/]+/\.\./~', '/', $path); // 替换 /../
    return $baseParts['scheme'] . '://' . $baseParts['host'] . $path;
}


if(!function_exists("str_starts_with")){
    function str_starts_with($haystack, $needle): bool
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}
<?php

namespace Kyanag\Revter;

use Throwable;

class RouteUnmatchedException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "没有匹配的路由";
        parent::__construct($message, $code, $previous);
    }

}
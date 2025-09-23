<?php

namespace Kyanag\Revter\Core;

use Psr\Http\Message\RequestInterface;
use Throwable;

class RouteUnmatchedException extends \Exception
{

    protected $request = null;

    protected $vars = [];

    public function __construct(RequestInterface $request, $vars, Throwable $previous = null)
    {
        $this->request = $request;
        $this->vars = $vars;
        parent::__construct("没有匹配的路由", 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getVars(): array
    {
        return $this->vars;
    }
}
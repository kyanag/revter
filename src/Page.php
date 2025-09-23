<?php

namespace Kyanag\Revter;

use DiDom\Document;
use Kyanag\Revter\Libs\Html;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Page
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    protected $vars = [];


    /**
     * @var array
     */
    protected $documents = [
        'element' => null,
        'text' => null,
        'json' => null,
        'original' => null,
    ];

    public function __construct(RequestInterface $request, ResponseInterface $response, array $vars = [])
    {
        $this->request = $request;
        $this->response = $response;
        $this->vars = $vars;
    }


    /**
     * @param $topic
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getAttribute($topic, $key, $default = null)
    {
        switch ($topic){
            case "@vars":
                return value_get($this->vars, $key, $default);
            case "@route":
                return value_get($this->vars['__route'], "route.{$key}", $default);
            case "@xpath":
                return Html::findElements($this->getContentAsDocument(), $key, "xpath");
            case "@css":
                return Html::findElements($this->getContentAsDocument(), $key, "css");
            case "@request":
                list($type, $key) = explode(".", $key . ".", 3);
                if($key == ""){
                    $type = $key;
                    $key = "get";
                }
                return $this->getRequestAttribute($type, $key, $default);
            case "@response":
                list($type, $key) = explode(".", $key . ".", 3);
                if($key == ""){
                    $type = $key;
                    $key = "get";
                }
                return $this->getResponseAttribute($type, $key, $default);
        }
        return null;
    }

    /**
     * 获取请求中的参数
     * @param $topic
     * @param $key
     * @param $default
     * @return mixed|null
     */
    protected function getRequestAttribute($topic, $key, $default = null)
    {
        if($key === ""){
            $key = null;
        }
        switch ($topic){
            case "header":
                return $this->request->getHeaderLine($key);
            case "get":
                return value_get($this->request->getQueryParams(), $key, $default);
            case "post":
                $body = $this->request->getParsedBody();
                if(is_array($body)){
                    return value_get($body, $key, $default);
                }
                return null;
            case "cookie":
                return value_get($this->request->getCookieParams(), $key, $default);
        }
        return null;
    }


    protected function getResponseAttribute($topic, $key, $default = null)
    {
        if($key === ""){
            $key = null;
        }
        switch ($topic){
            case "cookie":
            case "get":
                return $this->getRequestAttribute($topic, $key, $default);

            case "header":
                return $this->response->getHeaderLine($key);
            case "status":
                return implode(" ", [
                    $this->response->getStatusCode(),
                    $this->response->getReasonPhrase(),
                ]);
        }
        return null;
    }

    /**
     * @return Document
     */
    public function getContentAsDocument()
    {
        if(!$this->documents['element']){
            $this->documents['element'] = new Document($this->getContentAsText());
        }
        return $this->documents['element'];
    }


    public function getContentAsJson()
    {
        if(!$this->documents['json']){
            $this->documents['json'] = json_decode($this->getContentAsText(), true);
        }
        return $this->documents['json'];
    }


    public function getContent()
    {
        if(!$this->documents['original']){
            $this->documents['original'] = $this->response->getBody()->getContents();
        }
        return $this->documents['original'];
    }

    public function getContentAsText()
    {
        return $this->getContent();
    }
}
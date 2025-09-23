<?php

namespace Kyanag\Revter\Libs\Html;

use DiDom\Document;
use DiDom\Element;
use DiDom\Exceptions\InvalidSelectorException;
use DiDom\Node;

class Html
{

    /**
     * 查找第一个元素
     * @param Document|Element|Node $node
     * @param string $selector
     * @param string $selector_type 选择器类型 xpath,css
     * @return Element|null
     */
    public static function findElement($node, string $selector, string $selector_type = "xpath"): ?object
    {
        $elements = static::findElements($node, $selector, $selector_type);
        if (count($elements) > 0) {
            return $elements[0];
        }
        return null;
    }


    /**
     * 查找元素组
     * @param Document|Element|Node $node
     * @param string $selector  选择器
     * @param string $selector_type 选择器类型 xpath,css
     * @return Element[]
     */
    public static function findElements($node, string $selector, string $selector_type = "xpath"): array
    {
        $elements = [];
        try {
            if ($selector_type == "xpath") {
                $elements = $node->xpath($selector);
            } elseif ($selector_type == "css") {
                $elements = $node->find($selector);
            }
        }catch (InvalidSelectorException $e) {
            //PASS
        }
        return $elements;
    }

    /**
     * 获取元素属性
     * @param Element|Node|\DOMNode|string $node
     * @param string $attr
     * @return ?string
     */
    public static function attr($node, string $attr): ?string
    {
        if(is_null($node)){
            return null;
        }
        if (is_string($node)) {
            return $node;
        }

        if ($node instanceof \DOMAttr) {
            switch ($attr) {
                case "html":
                case "innerHtml":
                case "outerHtml":
                    return $node->nodeValue;
                case "text":
                default:
                    return $node->value;
            }
        }

        switch ($attr) {
            case "html":
                return $node->html();
            case "innerHtml":
                return $node->innerHtml();
            case "outerHtml":
                return $node->outerHtml();
            case "text":
                return $node->text();
        }
        if ($node->isElementNode()) {
            return $node->attr($attr);
        }
        return null;
    }
}
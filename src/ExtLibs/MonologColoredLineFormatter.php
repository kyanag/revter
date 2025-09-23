<?php

namespace Kyanag\Revter\ExtLibs;

use Monolog\Logger;

class MonologColoredLineFormatter extends \Monolog\Formatter\LineFormatter
{
    // ANSI 颜色代码（兼容大部分终端）
    const ANSI_COLORS = [
        Logger::DEBUG     => "\033[37m",      // 灰色
        Logger::INFO      => "\033[36m",      // 青色
        Logger::NOTICE    => "\033[32m",      // 绿色
        Logger::WARNING   => "\033[33m",      // 黄色
        Logger::ERROR     => "\033[31m",      // 红色
        Logger::CRITICAL  => "\033[31;1m",    // 红色加粗
        Logger::ALERT     => "\033[35;1m",    // 紫色加粗
        Logger::EMERGENCY => "\033[41;1m",    // 红底白字
    ];
    const RESET_COLOR = "\033[0m";
    public function format(array $record): string
    {
        $message = parent::format($record); // 先获取原始日志行
        $color = self::ANSI_COLORS[$record['level']] ?? ''; // 根据日志级别选择颜色
        return $color . $message . self::RESET_COLOR;
    }
}

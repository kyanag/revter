<?php

namespace Kyanag\Revter;

use Kyanag\Revter\Core\Interfaces\ReadonlyQueueInterface;
use Kyanag\Revter\ExtLibs\MonologColoredLineFormatter;
use Kyanag\Revter\Libs\DataStorages\JsonlDataStorage;
use Kyanag\Revter\Libs\Queue\MemoryQueue;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Factory
{

    public static function makeLogger($name, $logfile, $stdout = true): LoggerInterface
    {
        $logger = new Logger($name);
        $streamHandler = new StreamHandler($logfile, Logger::DEBUG);
        $streamHandler->setFormatter(new LineFormatter("[%datetime%] %level_name%: %message%\n", "Y-m-d H:i:s", true));
        $logger->pushHandler($streamHandler);

        //输出到
        if($stdout){
            $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            $consoleHandler->setFormatter(new MonologColoredLineFormatter("[%datetime%] %level_name%: %message%\n", "Y-m-d H:i:s", true));
            $logger->pushHandler($consoleHandler);
        }
        return $logger;
    }

    public static function makeDataStorage($type, $configs = [])
    {
        switch ($type){
            case "jsonl";
                return new JsonlDataStorage($configs['dir']);
            default:
                throw new \Exception("Unknown data storage type: {$type}");
        }
    }


    /**
     * @param string $type
     * @param array $configs
     * @return ReadonlyQueueInterface
     * @throws \Exception
     */
    public static function makeQueue($type, $configs = [])
    {
        switch ($type){
            case "memory":
                return new MemoryQueue();
            default:
                throw new \Exception("Unknown queue type: {$type}");
        }
    }
}
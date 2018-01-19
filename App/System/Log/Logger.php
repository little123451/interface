<?php
namespace App\System\Log;

class Logger {

    private static $instance;
    private static $logger;

    private function __construct($name){
        self::$logger = \Logger::getLogger($name);
    }

    /**
     * 单例工厂
     *
     * @param $name
     * @return Logger
     */
    static public function getLogger($name){
        if (self::$instance == null) {
            self::$instance = new Logger($name);
        }
        return self::$instance;
    }

    public function debug($msg){
        //self::$logger->debug($msg);
    }

    public function info($msg){
        self::$logger->info($msg);
    }

    public function warn($msg){
        self::$logger->warn($msg);
    }

    public function error($msg){
        self::$logger->error($msg);
    }

}
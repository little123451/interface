<?php
namespace App\System\Configure;

/**
 * 配置读取模块
 *
 * Class Config
 * @package App\System\Configure
 */
class Config {

    private static $config = NULL;

    /**
     * 加载配置参数
     */
    static public function loadConfig(){

        //防止重复加载
        if (self::$config !== NULL) return;

        $configDir = dir(CONFIG_DIR);
        $config = array();

        while($fileName = $configDir->read()){
            $key = str_replace('.php','',$fileName);
            $require = CONFIG_DIR.DIRECTORY_SEPARATOR.$fileName;
            if (is_file($require)) {
                $config[$key] = require_once $require;
            }
        }

        self::$config = $config;
    }

    /**
     * 加载对应文件的配置
     *
     * @param $configName
     * @return null
     */
    static public function load($configName){

        if ( ! array_key_exists($configName, self::$config) ) return null;

        return self::$config[$configName];

    }

    /**
     * 拦截配置请求
     *
     * @param $funcName string 配置文件
     * @param $param    string 配置key
     *
     * @return config
     */
    public function __call($funcName, $param){

        if ( ! array_key_exists($funcName, self::$config) ) return null;

        if ( empty($param) ) return self::$config[$funcName];

        if ( ! isset($param[0]) || ! is_string($param[0]) ) return null;

        if ( ! array_key_exists($param[0], self::$config[$funcName]) ) return null;

        return self::$config[$funcName][$param[0]];

    }


}
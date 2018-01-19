<?php
namespace App\System\Cache;
use App\System\Configure\Config;
use App\System\Log\Logger;
use App\System\Request\Request;

/**
 * 缓存模块
 *
 * Class Cache
 * @package App\System\Cache
 */
class Cache {

    //缓存句柄
    private static $mmc = null;

    //缓存开关
    private static $switch = true;

    //日志工具
    private static $logger;

    /**
     * 初始化缓存模块
     */
    static public function init(){
        $restConf = Config::load('rest');
        self::$logger = Logger::getLogger('Cache');

        //检查配置是否开启缓存
        if ( $restConf['rest_use_cache'] == false ) {
            self::$switch = false;
            return;
        }

        //初始化缓存实例
        self::$mmc = Memcache::create();

        //缓存实例初始化失败的话关闭缓存
        if ( self::$mmc == false ) {
            self::$switch = false;
            self::$logger->error('Cache init failed');
        }

    }

    /**
     * 获取请求缓存
     *
     * @param Request $request
     * @return bool|void
     */
    static public function getRequest(Request $request){

        //检查缓存开关
        if ( self::$switch == false ) return false;

        //对GET外的请求不作缓存处理
        if ( $request->getMethod() != 'GET' ) return false;

        $key = md5($request->toJSON());
        $value = self::$mmc->get($key);

        if ($value) self::$logger->debug('Load from cache');

        return $value;
    }

    /**
     * 设置请求缓存
     *
     * @param Request $request
     * @param $view
     */
    static public function saveRequest(Request $request, $view){

        //检查缓存开关
        if ( self::$switch == false ) return;

        //对GET外的请求不作缓存处理
        if ( $request->getMethod() != 'GET' ) return;

        //控制缓存持续时间
        $conf = Config::load('rest');
        $minute = $conf['rest_api_key_last'] > 10 ? 10 : $conf['rest_api_key_last'];
        $expire = $minute * 60;

        $key = md5($request->toJSON());
        self::$mmc->set($key, $view, $expire);

    }

    static public function help(){
        $dump = get_class_methods('Memcache');
        var_dump($dump);
    }

}
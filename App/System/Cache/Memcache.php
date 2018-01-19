<?php
namespace App\System\Cache;

use App\System\Configure\Config;

class Memcache {

    private $instance;

    /**
     * 初始化检查入口
     *
     * @return Memcache|bool
     */
    static public function create(){
        $conf = Config::load('memcache');
        if ( ! isset($conf['host']) ) return false;
        if ( ! isset($conf['port']) ) return false;
        return new Memcache($conf);
    }

    /**
     * 初始化
     *
     * @param $conf
     */
    private function __construct($conf){
        $this->instance = memcache_connect($conf['host'],$conf['port']);
    }

    /**
     * 查询缓存
     *
     * @param $key
     */
    public function get($key) {
        return  memcache_get($this->instance, $key);
    }

    /**
     * 写缓存
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value, $expire){
        return memcache_set($this->instance, $key, $value, 0, $expire);
    }

}
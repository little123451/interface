<?php
return array(

    /**
     * 是否开启API-KEY验证
     *
     * 在关闭验证的情况下,通过请求 POST /api/user/key 可以自动生成key
     *
     * 默认数据库表格:
     *   CREATE TABLE `access` (
     *       `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
     *       `key` VARCHAR(40) NOT NULL DEFAULT '',
     *       `secret` VARCHAR(40) NOT NULL DEFAULT '',
     *       `path` VARCHAR(50) NOT NULL DEFAULT '',
     *       `date_created` DATETIME DEFAULT NULL,
     *       `date_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     *       PRIMARY KEY (`id`)
     *    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    'rest_api_key_access' => false,

    //每个请求的有效时间
    'rest_api_key_last' => 5,

    //请求时间是否按毫秒数计算
    'rest_api_key_million_seconds' => true,

    /**
     * 是否开启API-KEY请求次数限制
     *
     * 可通过设置limit为 -1 取消某个接口的访问次数限制
     *
     * 默认数据库表格:
     *   CREATE TABLE `limits` (
     *       `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
     *       `key` VARCHAR(40) NOT NULL DEFAULT '',
     *       `limit` INT(11) NOT NULL DEFAULT 1000,
     *       `count` INT(11) NOT NULL DEFAULT 0,
     *       `path` VARCHAR(50) NOT NULL DEFAULT '',
     *       `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     *       PRIMARY KEY (`id`)
     *    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    'rest_api_key_limit' => false,

    //可以接受的请求方式
    'rest_allow_method' => array('get','post','put','patch','delete'),

    //允许的header参数
    'rest_accept_header' => array('X-API-KEY'),

    //是否开启缓存
    'rest_use_cache' => false
);
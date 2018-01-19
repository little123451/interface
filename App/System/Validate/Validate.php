<?php
namespace App\System\Validate;

/**
 * 认证主模块
 *
 * Class Validate
 * @package App\System\Validate
 */
class Validate {

    /**
     * 根据验证结果返回信息
     *
     * @param $msg
     */
    static protected function buildRes($msg){
        foreach($msg as $message){
            if ($message !== true) {
                return self::error($message);
            }
        }
        return self::success();
    }

    /**
     * 构造成功返回信息
     */
    static private function success(){
        $ret['success'] = true;
        $ret['msg'] = 'success';
        return $ret;
    }

    /**
     * 构造错误返回信息
     */
    static private function error($err){
        $ret['success'] = false;
        $ret['msg'] = $err;
        return $ret;
    }

}
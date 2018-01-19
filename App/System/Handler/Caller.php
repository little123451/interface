<?php
namespace App\System\Handler;
use App\System\Request\Request;

/**
 * 控制器唤醒装置
 *
 * Class Caller
 * @package App\System\Handler
 */
class Caller {

    static function call(Request $request){
        $data = $request->getRest();
        $method = $request->getMethod();

        $s = new $data['controller']($request);
        $funcName = strtolower($method).'_'.$data['func'];

        return $s->{$funcName}();
    }

}
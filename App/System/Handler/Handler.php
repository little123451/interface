<?php
namespace App\System\Handler;

use App\System\Cache\Cache;
use App\System\Format\Format;
use App\System\Request\Request;
use App\System\Validate\ValidateRequest;

/**
 * 请求处理入口类
 *
 * Class Handler
 * @package App\System\Handler
 */
class Handler {

    static public function Start(){

        //初始化请求
        $request = Request::create();

        try {

            //缓存处理
            $cache = Cache::getRequest($request);
            if ($cache) return $cache;

            //验证请求,因为验证有数据库操作,所以放在缓存后面,命中缓存的话就没有数据库操作了
            $validate = ValidateRequest::Validate($request);

            if ($validate['success'] == false) {
                return Format::format(null, $validate['msg'], false, $request->getParam('_view'));
            }

            //通过Caller和Request的内容唤起控制器进行处理
            $result = Caller::call($request);

            //格式化返回结果,准备输出
            $view = Format::format($result['data'], $result['msg'], $result['success'], $request->getParam('_view'));

            //对输出进行缓存
            Cache::saveRequest($request, $view);

            return $view;

        } catch (\Exception $e){

            //获取异常信息
            $err = $e->getMessage();

            //格式化返回结果,准备输出
            $view = Format::format(null, $err, false, $request->getParam('_view'));

            return $view;

        }
    }

}
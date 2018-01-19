<?php
namespace App\System\Validate;
use App\System\Adapter\MySQL;
use App\System\Configure\Config;
use App\System\Request\Request;

class ValidateRequest extends Validate {

    /**
     * 请求校验模块入口方法
     *
     * @param Request $request
     * @return mixed
     */
    static public function Validate(Request $request){
        $restConf = Config::load('rest');

        //如果有入口限制则先执行限制,减少方法扫描泄露的风险
        if ($restConf['rest_api_key_access']) {
            $msg[] = self::checkAccess($request);
        }
        if ($restConf['rest_api_key_limit']) {
            $msg[] = self::checkLimit($request);
        }

        $msg[] = self::checkMethod($request);
        $msg[] = self::checkRest($request);

        return self::buildRes($msg);
    }

    /**
     * 检查请求方式是否正常
     *
     * @param Request $request
     * @return bool|string
     */
    static private function checkMethod(Request $request){
        $restConf = Config::load('rest');
        $method = $request->getMethod();
        $allow = $restConf['rest_allow_method'];
        if ( ! in_array(strtolower($method), $allow) ) {
            return "Method [{$method}] not allowed";
        }
        return true;
    }

    /**
     * 检查请求类中的rest属性内容是否正常
     *
     * @param Request $request
     * @return bool|string
     */
    static private function checkRest(Request $request){
        $rest = $request->getRest();
        $method = $request->getMethod();

        //检查rest信息是否齐全
        if ( in_array('',$rest) || in_array(false,$rest) ) {
            return 'Rest Info not match';
        }

        //检查控制器文件是否存在
        $file = BASE_ROOT.$rest['path'].'.php';
        if ( ! file_exists($file) ) {
            return "Controller [{$rest['path']}] not found";
        }

        //检查对应类是否存在
        require_once $file;
        if ( ! class_exists($rest['controller']) ) {
            return "Controller [{$rest['controller']}] not found";
        }

        //检查对应方法是否存在
        $functions = get_class_methods($rest['controller']);
        $funcName = strtolower($method).'_'.$rest['func'];
        if ( ! in_array($funcName, $functions) ) {
            return "[{$method}] [{$rest['controller']}] [{$rest['func']}] function not exists";
        }

        return true;
    }

    /**
     * 检查接口请求权限
     *
     * @param Request $request
     * @return bool|string
     */
    static private function checkAccess(Request $request){
        $header = $request->getHeader();
        $param = $request->getParam();
        $path = $request->getRest('path');
        $restConf = Config::load('rest');

        $db = new MySQL('access');

        //检查参数是否齐全
        if ( ! isset($header['X-API-KEY']) || ! isset($param['token']) || ! isset($param['stamp']) ) {
            return 'Please set up your access info ( API-KEY \ token \ stamp )';
        }

        //检查时间戳是否有效
        $fix = $restConf['rest_api_key_million_seconds'] ? 1000 : 1;
        $lastTime = intval($restConf['rest_api_key_last']) ? 5 : $restConf['rest_api_key_last'];
        if (time() * $fix - $param['stamp'] > $lastTime * 60 * $fix ) {
            return 'Request expire';
        }

        //检查API-KEY是否存在
        $key = $db->quote($header['X-API-KEY']);
        $keyData = $db->query("SELECT * FROM `access` WHERE `key` = '{$key}'");
        if ( empty($keyData) || $keyData[0]['key'] != $key ) {
            return 'API KEY not exists';
        }

        //检查token是否有效
        $token = md5($header['X-API-KEY'].$param['stamp'].$keyData[0]['secret']);
        if ( $token != $param['token'] ) {
            return 'token not validate';
        }

        //检查API-KEY对应权限是否足够
        $reqPath = str_replace(DIRECTORY_SEPARATOR,'/',$path);
        $authPath = preg_quote($keyData[0]['path']);
        if ( ! preg_match('|^'.$authPath.'|',$reqPath) ) {
            return "[{$reqPath}] not authorize";
        }

        return true;
    }

    /**
     * 检查接口请求次数限制
     *
     * @param Request $request
     * @return bool
     */
    static private function checkLimit(Request $request){
        $limitConf = Config::load('rest');
        $key = $request->getHeader('X-API-KEY');
        $path = $request->getRest('path');
        $db = new MySQL('limits');

        //没有开启API-KEY验证
        if ($limitConf['rest_api_key_access'] == false) {
            return true;
        }

        //没有开启API-KEY限制
        if ($limitConf['rest_api_key_limit'] == false) {
            return true;
        }

        //检查限制信息是否存在
        $key = $db->quote($key);
        $path = str_replace(DIRECTORY_SEPARATOR,'/',$path);
        $keyData = $db->query("SELECT * FROM `limits` WHERE `key` = '{$key}' AND `path` = '{$path}' AND ( `limit` > `count` OR `limit` = -1 )");
        if ( empty($keyData) ) {
            return "Request limited : [{$key}] [{$path}]";
        }

        //目前命中缓存并不累计访问次数
        $db->query("UPDATE `limits` SET `count` = `count` + 1 WHERE `key` = '{$key}' AND `path` = '{$path}'");

        return true;
    }

}
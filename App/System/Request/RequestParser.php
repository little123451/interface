<?php
namespace App\System\Request;

/**
 * 解析请求内容
 *
 * Class RequestParser
 * @package App\System\Request
 */
class RequestParser {

    /**
     * 获取请求方法
     *
     * @return mixed
     */
    static public function parseMethod(){
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        return $method;
    }

    /**
     * 构造rest信息
     *
     * @return array
     */
    static public function parseRest(){

        $ret = array(
            'func' => false,
            'controller' => false,
            'path' => false
        );

        if (!isset($_SERVER['PATH_INFO'])) return $ret;
        $pathInfo = $_SERVER['PATH_INFO'];

        $path = explode('/',$pathInfo);
        $length = count($path);

        $ret['func'] = $path[$length - 1];
        unset($path[$length - 1]);

        $ret['controller'] = ucfirst($path[$length - 2]);

        $ret['path'] = implode(DIRECTORY_SEPARATOR,$path);

        return $ret;
    }

    /**
     * 获取header中的参数
     *
     * @return array
     */
    static public function parseHeader(){
        $header = array();

        $accept = array('X-API-KEY');

        foreach($accept as &$acceptKey){
            $acceptKey = str_replace('-','_',$acceptKey);
        }

        foreach($_SERVER as $key => $value){
            if (preg_match('/^HTTP_(.*)$/i',$key,$match) && in_array($match[1],$accept)) {
                $paramKey = str_replace('_','-',$match[1]);
                $header[$paramKey] = $value;
            }
        }

        return $_SERVER;
    }

    /**
     * 解析请求参数
     *
     * @return array
     */
    static public function parseParam(){
        $param = $_REQUEST;
        return $param;
    }

    /**
     * 解析BODY
     *
     * @return array
     */
    static public function parseBody(){
        $param = array();

        $body = file_get_contents('php://input');
        $jsonFlag = json_decode($body,true);

        //判断是否为json格式的body
        if ( ! is_null($jsonFlag) ) {
            $param = $jsonFlag;
            ksort($param);
        }

        return $param;
    }

}
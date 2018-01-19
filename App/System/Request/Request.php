<?php
namespace App\System\Request;

/**
 * 请求实体
 *
 * Class Request
 * @package App\System\Request
 */
class Request {

    /**
     * 请求方法
     *
     * @var
     */
    private $method;

    /**
     * 请求BODY
     *
     * @var
     */
    private $body;

    /**
     * 请求参数
     *
     * @var
     */
    private $param;

    /**
     * header信息
     *
     * @var
     */
    private $header;

    /**
     * RESTful信息
     *
     * @var
     */
    private $rest;

    /**
     * 请求构造工厂
     *
     * @return Request
     */
    static public function create(){
        $req['method'] = RequestParser::parseMethod();
        $req['body'] = RequestParser::parseBody();
        $req['param'] = RequestParser::parseParam();
        $req['header'] = RequestParser::parseHeader();
        $req['rest'] = RequestParser::parseRest();
        $instance = new Request($req);
        return $instance;
    }

    /**
     * 初始化
     *
     * @param $request
     */
    private function __construct($request){
        $this->method = $request['method'];
        $this->body = $request['body'];
        $this->param = $request['param'];
        $this->rest = $request['rest'];
        $this->header = $request['header'];
    }

    /**
     * 调试数据
     *
     * @return mixed
     */
    public function dump($key = null){
        $dump = array(
            'method' => $this->method,
            'param' => $this->param,
            'rest' => $this->rest,
            'header' => $this->header
        );
        if ($key == null || !array_key_exists($key,$dump)) {
            var_dump($dump);
        } else {
            var_dump($dump[$key]);
        }
    }

    /**
     * 将Request的内容转换成JSON格式
     *
     * @return string
     */
    public function toJSON(){
        $dump = array(
            'method' => $this->method,
            'param' => $this->param,
            'rest' => $this->rest,
            'header' => $this->header
        );
        $json = json_encode($dump);
        return $json;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     */
    public function getBody($key = null, $default = null)
    {
        if ($key == null) {
            return $this->body;
        } elseif (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        } else return $default;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     */
    public function getParam($key = null, $default = null)
    {
        if ($key == null) {
            return $this->param;
        } elseif (array_key_exists($key, $this->param)) {
            return $this->param[$key];
        } else return $default;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     */
    public function getHeader($key = null, $default = null)
    {
        if ($key == null) {
            return $this->header;
        } elseif (array_key_exists($key, $this->header)) {
            return $this->header[$key];
        } else return $default;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     */
    public function getRest($key = null, $default = null)
    {
        if ($key == null) {
            return $this->rest;
        } elseif (array_key_exists($key, $this->rest)) {
            return $this->rest[$key];
        } else return $default;
    }

}
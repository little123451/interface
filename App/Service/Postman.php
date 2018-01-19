<?php
namespace App\Service;

use App\System\Configure\Config;

class Postman {

    static private $retry = 0;

    private $url;
    private $query;
    private $headers;

    /**
     * 使用链接对请求进行初始化
     *
     * @param $url string  请求链接
     */
    public function __construct($url){
        $conf = Config::load('service');
        self::$retry = $conf['postman']['retry'];
        $this->url = trim($url);
        $this->initQuery($url);
        $this->addHeader('Content-Type','application/json;charset=UTF-8');
    }

    /*
     * 根据初始化的 url 对 query 参数进行初始化
     *
     * @param $url string  请求链接
     */
    private function initQuery($url){
        $meta = parse_url($url);
        if ( ! array_key_exists('query',$meta) ) {
            $this->query = array();
            return;
        }
        $temp = array();
        $arr = explode('&',$meta['query']);
        foreach($arr as $param){
            $kv = explode('=', $param, 2);
            if (count($kv) == 2) $temp[$kv[0]] = $kv[1];
        }
        $this->query = $temp;
    }


    /**
     * 设置 query 参数(数组)
     *
     * @param $query array 参数数组
     * @param string $type string
     *      'cover':完全覆盖 , 'merge':合并覆盖, 'extend':扩展
     * @return bool
     */
    public function setQuery($query, $type = 'cover'){

        //检验传入的 query 参数是否合法
        foreach($query as $key => $value){
            if (!is_string($key) || !is_string($value))
                return false;
        }

        switch ($type) {
            case 'cover' : $this->query = $query; break;
            case 'merge' : $this->query = array_merge($this->query, $query); break;
            case 'extend' : $this->query = array_merge($query, $this->query); break;
            default: break;
        }

        return true;
    }

    /**
     * 设置 query 参数(单个)
     *
     * @param $key string query的KEY
     * @param $value string query的值
     * @param string $type
     *      'cover':覆盖 , 'extend':扩展
     * @return bool
     */
    public function addQuery($key, $value, $type = 'cover'){
        if (!is_string($key) || !is_string($value)) return false;

        switch ($type) {
            case 'cover' : $this->query[$key] = $value; break;
            case 'extend' :
                if ( ! array_key_exists($key,$this->query) )
                    $this->query[$key] = $value;
                break;
            default: break;
        }

        return true;
    }

    /**
     * 设置请求头参数(数组)
     *
     * @param $headers array 参数数组
     * @param string $type string
     *      'cover':完全覆盖 , 'merge':合并覆盖, 'extend':扩展
     * @return bool
     */
    public function setHeader($headers, $type = 'cover'){

        //检验传入的 header 参数是否合法
        foreach($headers as $key => $value){
            if (!is_string($key) || !is_string($value))
                return false;
        }

        switch ($type) {
            case 'cover' : $this->headers = $headers; break;
            case 'merge' : $this->headers = array_merge($this->headers, $headers); break;
            case 'extend' : $this->headers = array_merge($headers, $this->headers); break;
            default: break;
        }

        return true;
    }

    /**
     * 设置请求头(单个)
     *
     * @param $key string header的KEY
     * @param $value string header的值
     * @param string $type
     *      'cover':覆盖 , 'extend':扩展
     * @return bool
     */
    public function addHeader($key, $value, $type = 'cover'){
        if (!is_string($key) || !is_string($value)) return false;

        switch ($type) {
            case 'cover' : $this->headers[$key] = $value; break;
            case 'extend' :
                if ( ! array_key_exists($key,$this->headers) )
                    $this->headers[$key] = $value;
                break;
            default: break;
        }

        return true;
    }

    /**
     * 发送 POST 请求
     *
     * @param $data array|string|object 需要post的数据
     * @param array $opt 请求设置
     * @param bool $detail 是否需要返回具体信息
     * @return mixed
     */
    public function POST($data, $opt = array(), $detail = false){
        return self::send('POST', $data, $opt, $detail);
    }

    /**
     * 发送 GET 请求
     *
     * @param array $opt 请求设置
     * @param bool $detail 是否需要返回具体信息
     * @return mixed
     */
    public function GET($opt = array(), $detail = false){
        return self::send('GET', array(), $opt, $detail);
    }

    /* 将 query 参数加载到 url 中 */
    private function loadQuery(){
        $query = '';

        foreach( $this->query as $key => $value){
            $param = $key.'='.$value;
            $query .= $param.'&';
        }

        $this->url .= '?'.trim($query,'&');
    }

    /*  格式化 header */
    private function loadHeader(){
        $header = array();
        foreach($this->headers as $key => $value) {
            $temp = $key.':'.$value;
            $header[] = $temp;
        }
        $this->headers = $header;
    }

    /**
     * 请求发送主方法
     *
     * @param $method string 请求方法
     * @param $data array|string|object 请求数据
     * @param $opt array 请求设置
     * @param bool $detail 是否需要返回详细信息
     * @return mixed
     */
    private function send($method, $data, $opt, $detail = false){
        $this->loadQuery();
        $this->loadHeader();
        $retry = self::$retry;

        $ch = curl_init(); //初始化CURL句柄
        curl_setopt($ch, CURLOPT_URL, $this->url); //设置请求的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);//设置HTTP头信息
        if ( $method != 'GET' ) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串

        do {
            $contents = curl_exec($ch);//执行预定义的CURL
            $msg = curl_getinfo($ch); $retry--;
        } while ( $msg['http_code'] == 0 && $retry > 0);

        curl_close($ch);

        if ($detail) {
            $msg['html'] = $contents;
            $msg['retry'] = $retry;
            return $msg;
        } else {
            return $contents;
        }
    }

}
<?php
namespace App\Service;

use App\System\Configure\Config;

class Baidu {

    /**
     * 百度翻译入口
     *
     * @param $str string 需要翻译的字符串
     * @param string $from 需要翻译的语言
     * @param string $to 翻译的目标语言
     * @param bool $detail 是否需要返回具体信息
     * @return array|bool|string
     */
    public static function translate($str, $from = 'auto', $to = 'auto', $detail = false){

        $conf = Config::load('service');
        $url = $conf['baidu']['translate_url'];
        $apiKey = $conf['baidu']['translate_api_key'];

        $req = new Postman($url);
        $body = array(
            'from' => $from,
            'to' => $to,
            'client_id' => $apiKey,
            'q' => $str
        );
        $json = $req->POST($body);
        $res = json_decode($json, true);

        if ($detail) return self::returnDetail($res);
            else return self::returnNormal($res);
    }

    /**
     * 返回详细翻译信息
     *
     * @param $res array 百度翻译接口返回的数据
     * @return array
     */
    private static function returnDetail($res){
        if ( array_key_exists('error_code',$res) ) {
            return array(
                'success' => false,
                'msg' => $res['error_msg']
            );
        } else {
            return array(
                'success' => true,
                'msg' => $res['trans_result']
            );
        }
    }

    /**
     * 只返回翻译具体结果
     *
     * @param $res array 百度翻译接口返回的数据
     * @return bool|string
     */
    private static function returnNormal($res){
        $ret = '';
        if ( ! array_key_exists('trans_result',$res) ) return false;
        foreach($res['trans_result'] as $record ){
            $ret .= $record['dst']."\n";
        }
        return trim($ret);
    }

}
<?php

if (!function_exists('buildResp')) {
    /**
     * 构造Controllers的返回
     *
     * @param $data         数据内容
     * @param string $msg   返回信息
     * @param bool $success 返回状态
     * @return mixed
     */
    function buildResp($data, $msg = 'success', $success = true){
        $ret['data'] = $data;
        $ret['msg'] = $msg;
        $ret['success'] = $success;
        return $ret;
    }
}


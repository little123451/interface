<?php
namespace App\System\Format;

/**
 * 格式化输出模块
 *
 * Class Format
 * @package App\System\Format
 */
class Format {

    /**
     * 输出格式化入口方法
     *
     * @param array|object $data    数据
     * @param string $msg           信息
     * @param boolean $success      成功状态
     * @param string $type          格式化类型
     * @return string
     */
    static public function format($data, $msg, $success, $type = 'json'){

        if ($success !== false) $success = true;
        $msg = (String)$msg;

        $ret = array(
            'data' => $data,
            'msg' => (String)$msg,
            'success' => $success
        );

        switch ($type) {
            //jsonPrettyPrint
            case 2 : $res = self::jsonPrettyPrint($ret);break;
            //printRPrint
            case 3 : $res = self::printRPrint($ret);break;
            //varDumpPrint
            case 4 : $res = self::varDumpPrint($ret);break;
            //jsonEncode
            default : $res = json_encode($ret,JSON_PRETTY_PRINT);
        }

        return $res;
    }

    /**
     * 使用highlight.js美化JSON输出
     *
     * @param $data
     * @return string
     */
    static private function jsonPrettyPrint($data){
        $formatData = json_encode($data,JSON_PRETTY_PRINT);
        $ret = <<<EOF
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0/styles/default.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
</head>
<body>
<pre><code class="json">{$formatData}</code></pre>
</body>
</html>
EOF;
        return $ret;
    }

    /**
     * 使用var_dump格式输出
     *
     * @param $data
     * @return string
     */
    static private function varDumpPrint($data){
        ob_start();
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    /**
     * 使用print_r格式输出
     *
     * @param $data
     * @return string
     */
    static private function printRPrint($data){
        ob_start();
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

}
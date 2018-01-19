<?php
class Crawl extends API_Controller{

    /**
     * 爬虫模块
     *
     * Example
     *
     * http://localhost/dillsyInterface/index.php/core/crawl/page?url=http://www.sundul.com
     * BODY:{"news":{"filter":[],"inner":["<div class=\"td_block_wrap td_block4\">[\\s\\S]*?<!-- .\\/block1 -->","<div class=\"span6\">[\\s\\S]*?<!-- .\\/span6 -->"],"fetchObject":{"img":"<img.*?src=\"(.*?)\"","title":"<a itemprop=\"url\".*?>(.*?)<","stamp":"datetime=\"(.*?)\""}},"link":{"filter":[],"inner":["<a.*?>"],"fetchObject":{"url":"<a href=\"(.*?)\""}}}
     *
     * @return mixed
     */
    public function post_page(){
        $ret = array();

        $url = $this->request->getParam('url');
        $jsonRule = $this->request->getBody();
        $html = file_get_contents($url);
        $temp = (array)$html;

        foreach($jsonRule as $key => $rule){

            $temp = $this->filter($temp, $rule['filter']);
            $temp = $this->inner($temp, $rule['inner']);
            $temp = $this->fetchObject($temp, $rule['fetchObject']);

            $ret[$key] = $temp;
            $temp = (array)$html;

        }

        return buildResp($ret);

    }

    /**
     * 滤除正则匹配的内容
     *
     * @param $htmlBlocks
     * @param $rule
     * @return mixed
     */
    private function filter($htmlBlocks, $rule){
        foreach($htmlBlocks as &$block) {
            foreach ($rule as $pattern) {
                $pattern = str_replace('/','\\/',$pattern);
                $pattern = '/' . $pattern . '/';
                $block = preg_replace($pattern, '', $block);
            }
        }
        return $htmlBlocks;
    }

    /**
     * 收敛正则匹配的内容
     *
     * @param $html
     * @param $rule
     * @return array
     */
    private function inner($htmlBlocks, $rule){
        $ret = array();

        foreach($rule as $pattern){
            $pattern = str_replace('/','\\/',$pattern);
            $pattern = '/'.$pattern.'/';

            //抽取各个区块中匹配的内容进行内敛
            foreach($htmlBlocks as &$block){
                $flag = preg_match_all($pattern,$block,$temp);
                if ($flag) $ret = array_merge($ret,$temp[0]);
            }

            //更新htmlBocks,进行下一次收敛
            $htmlBlocks = $ret;$ret = array();
        }

        return $htmlBlocks;
    }


    /**
     * 根据规则匹配出对应的内容存放在字段中
     *
     * @param $htmlBlocks
     * @param $rule
     * @return array
     */
    private function fetchObject($htmlBlocks, $rule){
        $container = array();

        foreach($htmlBlocks as &$block){
            $temp = array();

            //对区块中逐条进行匹配赋值
            foreach($rule as $key => $pattern){
                $pattern = str_replace('/','\\/',$pattern);
                $pattern = '/'.$pattern.'/';
                $flag = preg_match($pattern,$block,$match);
                if ($flag) $temp[$key] = $match[1];
            }

            //如果该区块匹配到内容,则保存
            if (!empty($temp)) $container[] = $temp;

        }
        return $container;
    }

}
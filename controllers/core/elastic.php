<?php
class Elastic extends API_Controller {

    /**
     * 获取单个对象
     *
     * @return mixed
     */
    public function get_object(){

        //GET http://localhost/dillsyInterface/index.php/core/elastic/object?_index=megacorp&_type=employee&_id=1

        $index = $this->request->getParam('_index','data');
        $type = $this->request->getParam('_type','news');
        $id = $this->request->getParam('_id');

        $client = new \App\System\Adapter\ElasticSearch();
        $params = array(
            'index' => $index,
            'type' => $type,
            'id' => $id
        );
        $esResp = $client->get($params);
        $ret = $this->buildGetObject($esResp);
        return buildResp($ret);
    }

    /**
     * 插入数据
     *
     * @return mixed
     */
    public function post_object(){

        //POST http://localhost/dillsyInterface/index.php/core/elastic/object?_index=megacorp&_id=5&_type=employee
        //BODY:{"first_name":"John","last_name":"Smith","ages":[],"about":"I love to go rock climbing","_lists":[{"id":"facebook","score":96},{"id":"twitter","score":25}]}

        $es = new \App\System\Adapter\ElasticSearch();
        $index = $this->request->getParam('_index','data');
        $type = $this->request->getParam('_type','news');
        $id = $this->request->getParam('_id');

        $decodeData = $this->request->getBody();
        $listsData = $decodeData['_lists'];
        unset($decodeData['_lists']);

        //检查data参数
        if ( $decodeData == null || ! is_array($decodeData) && ! is_object($decodeData) ) {
            return buildResp(null,'Param [data] type not match',false);
        }

        //处理lists参数
        $_lists = array();
        if ( $listsData != null && ! is_array($listsData) && ! is_object($listsData) ) {

            //lists校验失败
            return buildResp(null,'param [lists] type not match',false);

        } elseif ( $listsData != null ) {

            $lists = (array)$listsData;
            foreach($lists as $record) {

                //校验lists内字段内容
                $record = (array)$record;
                if ( ! isset($record['id']) || ! isset($record['score']) ) {
                    return buildResp($lists,'lists field name not match',false);
                }

                //字段内容校验通过
                $temp = array(
                    'id' => (String)$record['id'],
                    'score' => (Int)$record['score']
                );
                $_lists[] = $temp;

            }

        }

        //插入data数据
        $body = (array)$decodeData;
        $body['_lists'] = $_lists;
        $params['body'] = $body;
        $params['index'] = $index;
        $params['type'] = $type;
        if ($id !== null) $params['id'] = $id;
        $ret = $es->index($params);

        return buildResp($ret);
    }


    /**
     * 获取列表
     *
     * @return mixed
     */
    public function get_lists(){

        //GET http://localhost/dillsyInterface/index.php/core/elastic/lists?_index=megacorp&_type=employee&_listID=facebook

        $listID = $this->request->getParam('_listID');
        $index = $this->request->getParam('_index','data');
        $type = $this->request->getParam('_type','news');
        $order = $this->request->getParam('_order','desc');

        $client = new \App\System\Adapter\ElasticSearch();
        $body['query']['match']['_lists.id'] = $listID;
        $body['sort']['_lists.score']['order'] = $order;
        $params = array(
            'index' => $index, //'megacorp',
            'type' => $type, //'employee',
            'body' => $body
        );
        $esResp = $client->search($params);
        $ret = $this->buildSearchLists($esResp);
        return buildResp($ret);
    }

    /**
     * 搜索
     *
     * @return mixed
     */
    public function get_search(){
        $key = $this->request->getParam('kw');
        $index = $this->request->getParam('_index','data');
        $type = $this->request->getParam('_type','news');
        $client = new \App\System\Adapter\ElasticSearch();
        if ( empty($key) ) return buildResp(null,'Key word not set',false);
        $body['query']['filtered']['query']['match']['_all'] = $key;
        $params = array(
            'index' => $index, //'megacorp',
            'type' => $type, //'employee',
            'body' => $body
        );
        $esResp = $client->search($params);
        $ret = $this->buildSearchLists($esResp, 'search');
        return buildResp($ret);
    }

    /**
     * 预处理ES的返回
     *
     * @param $esResp
     * @return array
     */
    private function buildGetObject($esResp){

        $ret = array();
        $source = $esResp['_source'];

        //过滤掉ES自带返回的数据,保留原始数据返回
        foreach($source as $key => $value){
            if (!preg_match('/^_/',$key)) $ret[$key] = $value;
        }

        //补充ID返回
        $ret['_id'] = $esResp['_id'];

        //如果需要查看lists,返回lists
        if ($this->request->getParam('_fetch_lists') == 1 && $esResp['_source']['_lists']){
            $ret['_lists'] = $esResp['_source']['_lists'];
        }

        return $ret;
    }

    /**
     * 构造列表返回
     *
     * @param $esResp
     * @param $type
     * @return array
     */
    private function buildSearchLists($esResp, $type = 'lists'){

        $ret = array();
        $hits = $esResp['hits']['hits'];

        //提取返回的数据内容
        foreach($hits as $objectResp){
            $object = $this->buildGetObject($objectResp);

            if ($type == 'lists')
                $object['_sort'] = $objectResp['sort'][0];
            elseif ($type == 'search')
                $object['_sort'] = $objectResp['_score'];

            $ret[] = $object;
        }


        return $ret;
    }

}
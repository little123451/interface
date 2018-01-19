<?php
class Data extends API_Controller {

    /**
     *   CREATE TABLE `data_news` (
     *       `id` VARCHAR(40) NOT NULL,
     *       `content` TEXT NOT NULL DEFAULT '',
     *       `create_time` DATETIME DEFAULT NULL,
     *       `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     *       PRIMARY KEY (`id`)
     *    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    private static $dataTableName = 'data_news';

    /**
     *   CREATE TABLE `lists_news` (
     *       `id` VARCHAR(40) NOT NULL,
     *       `list` VARCHAR(40) NOT NULL DEFAULT '',
     *       `score` INT(11) NOT NULL DEFAULT 1,
     *       PRIMARY KEY (`id`,`list`)
     *    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    private static $listsTableName = 'lists_news';

    /**
     * 例子
     *
     * @return mixed
     */
    public function get_example(){
        $insertExample = array(
            'method' => 'POST',
            'path' => '/api/data/object',
            'param' => array(
                'id' => 'people-0001',
            ),
            'body' =>'{"name":"Edward","age":23,"_lists":[{"id":"vip","score":56}]}',
            'bodyDecode' => json_decode('{"{"name":"Edward","age":23,"_lists":[{"id":"vip","score":56}]}')
        );
        $getObjectExample = array(
            'method' => 'GET',
            'path' => '/api/data/object',
            'param' => array(
                'id' => 'people-0001'
            )
        );
        $getListsExample = array(
            'method' => 'GET',
            'path' => '/api/data/lists',
            'param' => array(
                'listID' => 'vip'
            )
        );
        $example = array(
            'InsertExample' => $insertExample,
            'GetObjectExample' => $getObjectExample,
            'GetListsExample' => $getListsExample
        );
        return buildResp($example);
    }

    /**
     * 插入数据
     *
     * @return mixed
     */
    public function post_object(){

        $db = new \App\System\Adapter\MySQL(self::$dataTableName);
        $id = $this->request->getParam('_id');

        $decodeData = $this->request->getBody();
        $lists = $decodeData['_lists'];
        unset($decodeData['_lists']);
        $dataString = json_encode($decodeData);

        //开启事务
        $db->beginTransaction();

        //检查data参数
        if ( $decodeData == null || ! is_array($decodeData) && ! is_object($decodeData) ) {
            return buildResp(null,'Param [data] type not match',false);
        }

        //构造ID
        if ( $id == null ) return buildResp(null,'id not set',false);

        //插入data数据
        $insert = array(
            'id' => $id,
            'content' =>  $dataString,
            'create_time' => date('Y-m-d H:i:s',time())
        );
        $rollBack = $db->insert($insert);
        if ($rollBack === false) {
            $db->rollBack();
            return buildResp(null,'data insert failed',false);
        }

        //处理lists参数
        $db->changeTable(self::$listsTableName);
        if ( $lists != null && ! is_array($lists) && ! is_object($lists) ) {

            //lists校验失败
            return buildResp(null,'param [lists] type not match',false);

        } elseif ( $lists != null ) {

            $lists = (array)$lists;
            foreach($lists as $record) {

                //校验lists内字段内容
                $record = (array)$record;
                if ( ! isset($record['id']) || ! isset($record['score']) ) {
                    return buildResp($lists,'lists field name not match',false);
                }

                //字段内容校验通过,插入数据
                $insert = array(
                    'id' => $id,
                    'list' => (String)$record['id'],
                    'score' => (Int)$record['score']
                );
                $rollBack = $db->insert($insert);
                if ( $rollBack === false ) {
                    $db->rollBack();
                    return buildResp($record,'lists data insert failed',false);
                }

            }

        }

        $ret = $db->commit();
        if ($ret === false) {
            return buildResp(null,'Transaction commit failed',false);
        } else {
            return buildResp($id);
        }
    }

    /**
     * 读取单条数据
     *
     * @return mixed
     */
    public function get_object(){
        $dataTable = self::$dataTableName;
        $db = new \App\System\Adapter\MySQL($dataTable);
        $id = $this->request->getParam('id');

        if ($id == null) {
            return buildResp(null,'param id not set',false);
        }

        $id = $db->quote($id);
        $query = "SELECT * FROM `{$dataTable}` WHERE `id` = '{$id}'";
        $fetch = $db->query($query);
        if ( $fetch == false || empty($fetch) || !isset($fetch[0]) ) {
            return buildResp(null,"id not found : [{$id}]");
        }

        $data = $fetch[0];
        $ret = json_decode($data['content']);
        if ($ret == null) {
            return buildResp($data,"data json decode fail",false);
        }
        $ret = (array)$ret;
        $ret['_id'] = $data['id'];
        $ret['_update_time'] = $data['update_time'];
        $ret['_create_time'] = $data['create_time'];

        return buildResp($ret);
    }

    /**
     * 读取列表数据
     *
     * @return mixed
     */
    public function get_lists(){
        $dataTable = self::$dataTableName;
        $listTable = self::$listsTableName;
        $db = new \App\System\Adapter\MySQL($dataTable);

        //检查listID参数
        $listID = $this->request->getParam('listID');
        if ( $listID == null ) {
            return buildResp(null,"param listID not set");
        }

        //获取list中对应的objectID
        $listID = $db->quote($listID);
        $query = "SELECT `id` FROM `{$listTable}` WHERE `list` = '{$listID}'";
        $data = $db->query($query);
        if ($data === false) {
            return buildResp(null,'lists query failed',false);
        }

        //处理获取到的objectID
        if ( empty($data) ) return buildResp(array());

        //构造查询内容
        $idSet = '';
        foreach ($data as $objectKey) $idSet .= '\'' . $objectKey['id'] . '\',';
        $idSet = trim($idSet, ',');
        $query = "SELECT * FROM `{$dataTable}` WHERE `id` IN ({$idSet})";
        $result = $db->query($query);

        //构造格式返回
        if ( $result == false ) {
            return buildResp(null,'data query failed',false);
        } else {
            $ret = array();
            foreach($result as $listData) {
                $temp = json_decode($listData['content']);
                $temp = (array)$temp;
                $temp['_id'] = $listData['id'];
                $temp['_update_time'] = $listData['update_time'];
                $temp['_create_time'] = $listData['create_time'];
                $ret[] = $temp;
            }
            return buildResp($ret);
        }

    }

    /**
     * 简易查询方法
     *
     * @return mixed
     */
    public function get_search(){
        $dataTable = self::$dataTableName;
        $db = new \App\System\Adapter\MySQL($dataTable);
        $key = $this->request->getParam('kw');

        if ( empty($key) ) return buildResp(null,'Key word not set',false);
        $key = urldecode($key);

        $key = trim(json_encode($db->quote($key)),'"');
        $key = str_replace('\\','_',$key);
        $key = str_replace('\'','_',$key);
        $sql = "SELECT * FROM `{$dataTable}` WHERE `content` like '%{$key}%'";

        $data = $db->query($sql);

        return buildResp($data);

    }

    /**
     * 测试方法
     *
     * @return mixed
     */
    public function get_test(){
        $data['param'] = $this->request->getParam();
        $data['header'] = $this->request->getHeader();
        $data['method'] = $this->request->getMethod();
        $data['body'] = $this->request->getBody();
        $data['rest'] = $this->request->getRest();
        return buildResp($data);
    }

    public function post_test(){
        return $this->get_test();
    }

    public function put_test(){
        return $this->get_test();
    }

    public function patch_test(){
        return $this->get_test();
    }

    public function delete_test(){
        return $this->get_test();
    }

}
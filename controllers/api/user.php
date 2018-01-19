<?php
class User extends API_Controller {

    public function put_key(){

        $db = new \App\System\Adapter\MySQL('access');
        $db->beginTransaction();
        $accessData = array(
            'key' => md5(time()),
            'secret' => sha1(uniqid(time())),
            'path' => '/',
            'date_created' => date('Y-m-d H:i:s', time())
        );
        $flag[] = $db->insert($accessData);

        $db->changeTable('limits');
        $limitData = array(
            'key' => $accessData['key'],
            'limit' => 1000,
            'path' => '/',
        );
        $flag[] = $db->insert($limitData);

        if ( in_array(false,$flag) ) {
            $db->rollBack();
        } else {
            $db->commit();
        }

        $data['access'] = $accessData;
        $data['limit'] = $limitData;

        return buildResp($data);

    }

}
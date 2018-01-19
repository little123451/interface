<?php
class Service extends API_Controller {

    public function get_translate(){
        $query = $this->request->getParam('q');
        $from = $this->request->getParam('f','auto');
        $to = $this->request->getParam('t','auto');
        $ret = \App\Service\Baidu::translate($query,$from,$to,true);
        if ( $ret['success'] ) {
            return buildResp($ret['msg']);
        } else {
            return buildResp(null,$ret['msg'],false);
        }
    }

}
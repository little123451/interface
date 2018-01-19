<?php
class Example extends API_Controller {

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
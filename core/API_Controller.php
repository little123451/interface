<?php
class API_Controller {

    protected $request;

    public function __construct(\App\System\Request\Request $request){
        $this->request = $request;
    }

}
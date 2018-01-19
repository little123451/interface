<?php
namespace App\System\Adapter;

use App\System\Configure\Config;

class ElasticSearch extends \Elasticsearch\Client{

    public function __construct($params = array()){
        $esConf = Config::load('elasticSearch');
        $string = $esConf['host'].':'.$esConf['port'];
        $params['hosts'] = array ($string);
        parent::__construct($params);
    }

}
<?php
namespace App\System\Adapter;

use App\System\Configure\Config;
use App\System\Log\Logger;

/**
 * MySQL适配器
 *
 * Class MySQL
 * @package App\System\Adapter
 */
class MySQL extends \PDO {

    private static $logger;
    private $table;

    /**
     * 初始化
     *
     * @param string $table
     */
    public function __construct($table = ''){
        self::$logger = Logger::getLogger('DB');
        $this->connect();
        $this->table = $table;
    }

    /**
     * 更换默认数据库表
     *
     * @param string $table
     */
    public function changeTable($table = ''){
        $this->table = $table;
    }

    /**
     * 链接数据库
     */
    private function connect(){
        $dbConfig = Config::load('database');
        $port = $dbConfig['port'];
        $host = $dbConfig['host'];
        $user = $dbConfig['user'];
        $pwd = $dbConfig['pwd'];
        $dbname = $dbConfig['db'];
        $dsn = "mysql:host={$host};dbname={$dbname};port={$port}";
        try {
            parent::__construct($dsn, $user, $pwd);
        }catch(\PDOException $error) {
            $msg = $error->getMessage();
            self::$logger->error($msg);
            die($msg);
        }
        parent::exec("set charset 'utf8'");
    }

    public function help(){
        $dump = get_class_methods('PDO');
        var_dump($dump);
        $dump = get_class_methods('PDOStatement');
        var_dump($dump);
    }

    /**
     * 数据插入
     *
     * @param array $insertData  插入数据
     * @param string $insertType 插入方式
     * @return bool|string
     */
    public function insert($insertData, $insertType = 'REPLACE'){

        //判断插入方式
        switch ($insertType) {
            case 'INSERT':$type = 'INSERT INTO';break;
            case 'IGNORE':$type = 'INSERT IGNORE';break;
            case 'REPLACE':$type = 'REPLACE INTO';break;
            default:$type = 'INSERT';
        }

        //构造sql语句
        $fields = '';$values = '';
        if ( ! is_array($insertData) ) return false;
        foreach($insertData as $key => $val){
            $fields .= '`'.$key.'`'.',';
            if ( is_numeric($val) ) {
                $values .= $val.',';
            } else {
                $val = $this->quote($val);
                $values .= '\''.$val.'\''.',';
            }
        }
        $fields = trim($fields,',');
        $values = trim($values,',');
        $sql = "{$type} `{$this->table}` ({$fields}) VALUES ({$values});";
        self::$logger->debug($sql);

        //执行sql语句
        $ret = parent::exec($sql);

        //处理异常
        if ($ret === false) {
            $msg = $this->errorInfo();
            self::$logger->error($msg[2]);
            return false;
        }

        //成功插入返回插入ID
        $id = parent::lastInsertId();

        return $id;
    }

    /**
     * 更新数据
     *
     * @param $updateData   需要更新的数据
     * @param $id           需要更新的记录ID
     * @return resource
     */
    public function updateByID($updateData, $id){

        //构造SQL语句
        $set = '';
        foreach($updateData as $field => $value){
            if (is_numeric($value)) {
                $set .= "`{$field}`={$value},";
            } else {
                $value = $this->quote($value);
                $set .= "`{$field}`='{$value}',";
            }
        }
        $set = trim($set,",");
        $sql = "UPDATE `{$this->table}` SET {$set} WHERE (`id` = '{$id}')";
        self::$logger->debug($sql);

        //执行查询
        $ret = parent::exec($sql);

        //处理异常情况
        if ( $ret === false ) {
            $err = $this->errorInfo();
            self::$logger->error($err[2]);
            return false;
        }

        return $ret;
    }

    /**
     * 直接执行语句查询
     *
     * @param $sql          查询语句
     * @return array|bool
     */
    public function query($sql){
        self::$logger->debug($sql);
        $statement = parent::query($sql);
        $err = parent::errorInfo();
        if ( ! empty($err[2]) ) {
            self::$logger->error($err[2]);
            return false;
        }
        $ret = array();
        while($row = $statement->fetch(\PDO::FETCH_ASSOC)){
            $ret[] = $row;
        }
        return $ret;
    }

    /**
     * quote函数微调
     *
     * @param string $string
     * @return string
     */
    public function quote($string, $paramtype = NULL){
        $quote = parent::quote($string, $paramtype);
        $quote = trim($quote,'\'');
        return $quote;
    }

}
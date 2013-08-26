<?php
class lib_datasource_db implements lib_datasource_interface
{
    protected static $instance = null;
    private $sqls = array();
    private $dbs = array();
    private $db = null;

    private function __construct(){}

    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    static public function getDb($source = 'mysql'){
        self::getInstance(); 
        return self::$instance->getDataSource('db',$source);
    }

    public function getDataSource($type = 'db',$source = 'mysql')
    {
        $ant = ant::getInstance();
        $config = $ant->getDbConfig();
        if($config){
            $db_instance_name = $config['db_type'].'_'.$config['db_driver'];
            if(isset(self::$instance->dbs[$db_instance_name])){
                return self::$instance->dbs[$db_instance_name];
            }
            $config['db_type'] = $source;
            $class_name = 'lib_datasource_drivers_'.$config['db_driver'];
            if(class_exists($class_name)){
                $obj = new $class_name();
                $obj->init($config);
                $this->db = $obj;
                self::$instance->dbs[$db_instance_name] = $this;
                return $this;
            }
        }
        return false;  
    }

    public function connect()
    {
        $this->db->connect();
    }

    public function query()
    {
        $args = func_get_args();
        if (count($args) == 2 && is_array($args[1])) {
            $sql = $args[0];
            $args = $args[1];
        } else {
            $sql = array_shift($args);
        }
        $this->connect();
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            if (defined('DEBUG')) {
                echo("Failed to prepare:" . $sql . "<br>");
                print_r($stmt->errorInfo());
                die();
            }
            return false;
        }
        //确保多余的参数被忽略
        preg_match_all('/\?/', $sql, $a);
        if (isset($a[0]) && $l = count($a[0])) {
            $args = array_splice($args, 0, $l);
        }
        if (!$this->db->execute($args)) {
            if (defined('DEBUG')) {
                echo("<br/>Failed to execute:<b style='color:red;'>" . $sql . "</b><br>");
                var_dump($this->db->driver->errorInfo(),$args);
die;
            }
            return false;
        }
        $this->stmt = $this->db->stmt;
        $this->sqls[] = array(
            'sql'=>$sql,
            'params'=>$args
        );
        return $this->stmt;
    }

    public function getSqlRecord()
    {
        return $this->sqls;
    }

    public function select()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        if ($stmt == false) {
            return array();
        }
        $data = $this->db->fetchAll();
        foreach($data as $k=>$row) {
            foreach($row as $k2=>$val) {
                if(is_resource($val)) {
                    $str = '';
                    while($s = fread($val,128)) {
                        $str .= $s;
                    }

                    fclose($val);
                    $data[$k][$k2] = $str;
                }
            }
        }
        return $data;
    }

    //只取一条数据
    public function selectOne()
    {
        $args = func_get_args();
        $rs = call_user_func_array(array($this, 'select'), $args);
        return (!empty($rs) ? $rs[0] : false);
    }

    //只取一条数据和它的第一列
    public function getOne()
    {
        $args = func_get_args();
        $rs = call_user_func_array(array($this, 'selectOne'), $args);
        return ($rs === false) ? $rs : array_shift($rs);
    }

    public function insert()
    {
        $args = func_get_args();
        $rs = call_user_func_array(array($this, 'query'), $args);
        return ($rs === false) ? $rs : $this->db->getLastInsertId(); 
    }

    public function delete()
    {
        $args = func_get_args();
        $rs = call_user_func_array(array($this, 'query'), $args);
        return ($rs === false) ? $rs : $this->db->getAffectRow();
    }

    public function update()
    {
        $args = func_get_args();
        $rs = call_user_func_array(array($this, 'query'), $args);
        return ($rs === false) ? $rs : $this->db->getAffectRow();
    }

    public function selectLimit($sql, $start, $limit)
    {
        $db_type = $this->db->getDbType();
        if($db_type == 'mysql'){

        }else{ //oracle
            $end = $start + $limit;
            $sql = "
                select * from (
                    select o.*,ROWNUM RN
                    from ($sql) o
                    where ROWNUM <= $end
                ) where RN > $start
                ";
        }
        $args = func_get_args();

        array_shift($args);
        array_shift($args);
        array_shift($args);
        array_unshift($args, $sql);
        return call_user_func_array(array($this, 'select'), $args);
    }

    public function beginTransaction()
    {
        $this->connect();
        $rs = $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollBack(); 
    }
}

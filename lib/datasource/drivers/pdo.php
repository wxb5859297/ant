<?php
/**
 * pdo driver
 */

class lib_datasource_drivers_pdo{
    private $config = null;
    private $is_connect = false;
    public $driver = null;
    public $stmt = null;

    public function __construct($config = null)
    {
    }

    public function init($config)
    {
        $this->config = $config;
    }

    public function connect(){
        if($this->is_connect === false){
            $user = $this->config['db_user'];
            $pass = $this->config['db_pass'];
            $dbname = $this->config['db_name'];
            $dbhost = $this->config['db_host']; 
            $dbtype = $this->config['db_type'];
            $dblink = "$dbtype:host=$dbhost;dbname=$dbname"; 
            try{
                $this->driver = new PDO($dblink,$user,$pass);
            }catch(Exception $e){
                //todo what? 

            }
            if($this->driver){
                $rs = $this->driver->exec('set names utf8');
                $this->is_connect = ($rs === false) ? $rs : true;
            }
        }
        return $this->is_connect;
    }   

    public function prepare($sql)
    {
        $this->stmt = $this->driver->prepare($sql);
        return $this->stmt;
    }

    public function execute($params)
    {
        return $this->stmt->execute($params);  
    }

    public function getLastInsertId()
    {
        return $this->driver->lastInsertId(); 
    }

    public function getAffectRow()
    {
        return $this->stmt->rowCount();
    }

    public function fetchAll(){
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function beginTransaction(){
        return $this->driver->beginTransaction();
    }

    public function commit(){
        return $this->driver->commit();
    }

    public function rollBack(){
        return $this->driver->rollBack();
    }
}

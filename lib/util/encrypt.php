<?php
/** 加密、解密类
 * file: encrypt.php
 * Author: wuxiabing
 * Date: 13-8-19 Time: 下午1:59
 * Email: wuxiabing@myhexin.com
 */

class lib_util_encrypt
{
    static public $instance = null;
    private $encrypt_type = 'normal';


    private function __construct($params)
    {
        $this->init($params);
    }

    private function init($params)
    {
        //todo init by encrypt_type
    }

    public function getInstance($params)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($params);
        }
        return self::$instance;
    }

    public function encrypt()
    {
        //todo encrypt by encrypt_type
    }

    public function decrypt()
    {
        //todo encrypt by encrypt_type
    }
}
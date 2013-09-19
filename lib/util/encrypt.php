<?php
/** 加密、解密类
 * file: encrypt.php
 * Author: wuxiabing
 * Date: 13-8-19 Time: 下午1:59
 * Email: wuxiabing@myhexin.com
 */

class lib_util_encrypt
{
    static private $instance = null;
    private $encrypt_type = '';
    private $params = array();

    private function __construct($params = array(),$encrypt_type = 'md5')
    {
        $this->init($params,$encrypt_type);
    }

    public function init($params,$encrypt_type = 'md5')
    {
        $this->encrypt_type = $encrypt_type;
        $this->params = $params;
    }

    static public function getInstance($params = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new self($params);
        }
        return self::$instance;
    }

    public function encrypt()
    {
        switch($this->encrypt_type){
        case 'md5':
            return md5($this->params['string'].$this->params['key']);
        case 'sha':
            $hash_type = $this->params['hash_type'] ? $this->params['hash_type'] : 'sha256';
            return hash($hash_type,$this->params['string']);
        case 'rsa': //此处给的是支付宝客户端的验证方法
            $priKey = file_get_contents($this->params['private_key_file']);
            $res = openssl_get_privatekey($priKey);
            openssl_sign($this->params['data'], $sign, $res);
            openssl_free_key($res);
            return base64_encode($sign);
        case '3des':
            $mcrypt_type = MCRYPT_3DES;
            $cipher_mode = $this->params['mode'] ? $this->params['mode'] : 'ecb';
            $size = mcrypt_get_block_size($mcrypt_type,$cipher_mode);
            $pad = $size - (strlen($this->params['string']) % $size);
            $input = $this->params['string'] . str_repeat(chr($pad), $pad);
            $td = mcrypt_module_open($mcrypt_type, '', $cipher_mode, '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $this->key, $iv);
            $data = mcrypt_generic($td, $input);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            return base64_encode($data); //目前只支持base64,可以使用其他加密

        }    
    }

    public function decrypt()
    {
        switch($this->encrypt_type){
        case 'md5':
            return ($this->encrypt() == $this->params['encrypt_string']);
        case 'sha':
            return ($this->encrypt() == $this->params['encrypt_string']);
        case 'rsa':
            $pubKey = file_get_contents($this->params['public_key_file']);
            $res = openssl_get_publickey($pubKey);
            $result = (bool)openssl_verify($this->params['data'], base64_decode($this->params['sign']), $res);
            openssl_free_key($res);
            return $result;
        case '3des':
            return ($this->encrypt() == $this->params['encrypt_string']);
        }    
    }
}

<?php
/**
 * antr Ant 的标准组件,用于编写输入过滤
 * 这是完全独立的一个类,不依赖其他ant类
 * 每一个过滤函数都可以设置
 * 1、是否试必须的，必须的过滤会导致异常抛出，不执行接下来的任何代码，并且设置一条错误信息
，
 * 而非必须的测试，如果失败，可以给出一个默认值或者null，但是不会导致抛出异常，设置一条警>告信息
 * 2、信息模板，大多数已有过滤函数有自己的模板，但是你依然可以自己设置错误信息模板
 * 用法
 * 在不同脚本中使用(ant 中的用法)
 * $r->post('cid')->int(false,0)->save();//如果输入不是一个整数，用0做默认值
 * echo $r->post('cid')->value();
 * 直接获取处理后的值
 * $r->post('cid')->int(false,0)->save()->value();
 * 获取错误描述
 * $r->post()->getErrorString();
 * 注意：处理完毕后值必须调用save,任何一个函数都应该在(deal,get,post,cookie)函数被调用后使用
 *
 * @example          $r->get('cid')->int(false,0)->save();$r->value('cid','get');//0
 *
 */
class antr
{
    /**
     * @var antc $act
     */
    public $act = null;
    protected $_val = null;
    protected $_key = null;
    protected $_data = array();
    protected $_type = 'get';
    protected $_tpl = '';
    protected $_name = '';
    protected $error = array();
    protected $warning = array();
    public $find_all_errors = true;
    public $failed = false;

    public function passed()
    {
        return !$this->failed;
    }

    public function exec()
    {

    }

    public function deal($type, &$array = array(), $key = null, $name = '')
    {
        if ($this->getData($type, $key)) {
            return $this->keep($type, $key);
        }

        $this->_type = $type;
        if ($key !== null) {
            $this->_key = $key;
            if (isset($array[$key]))
                $this->_val = $array[$key];
            else
                $this->_val = null;
            if (empty($name)) {
                $this->_name = $key;
            } else {
                $this->_name = $name;
            }
        }
        $this->save();
        return $this;
    }

    public function save()
    {
        if (!isset($this->_data[$this->_type])) {
            $this->_data[$this->_type] = array();
        }

        $this->_data[$this->_type][$this->_key] = array($this->_val, $this->_name);
        return $this;
    }

    public function get($key = null, $name = '')
    {
        return $this->deal('get', $_GET, $key, $name);
    }

    public function post($key = null, $name = '')
    {
        return $this->deal('post', $_POST, $key, $name);
    }

    public function cookie($key = null, $name = '')
    {
        return $this->deal('cookie', $_COOKIE, $key, $name);
    }

    /**
     * 判断一个输入是否是int类型，可以是字符串int
     * @param bool $flag
     * @param null $error
     * @param null $errno
     * @return $this
     */
    public function int($flag = true, $error = null, $errno = null)
    {
        if (!is_int($this->_val) && preg_match('/^\d+$/', $this->_val) === 0) {
            $this->setError($flag, $error, $errno, 'int');
        }
        $this->save();
        return $this;
    }

    /*
     * 验证空输入，包括空字符长度和未设置的情况都算
     * @param null $error
     * @param null $errno
     * @return $this
     */
    public function isEmpty($flag = true, $error = null, $errno = null)
    {
        if ($this->_val === null || $this->_val === '') {
            $this->setError($flag, $error, $errno, 'isEmpty');
        }
        $this->save();
        return $this;
    }

    /**
     * 验证数字输入
     * @param bool $flag
     * @param null $error
     * @param null $errno
     * @return $this
     */
    public function number($flag = true, $error = null, $errno = null)
    {
        if (strlen($this->_val) > 0)
            if (!is_numeric($this->_val)) {
                $this->setError($flag, $error, $errno, 'number');
            }
        $this->save();
        return $this;
    }

    /**
     * 验证字符串长度,ascii
     * @param $flag
     * @param $max
     * @param int $min
     * @param null $error
     * @param null $errno
     * @return $this
     */
    public function length($flag, $max, $min = 1, $error = null, $errno = null)
    {
        $l = strlen($this->_val);
        if ($l < $min || $l > $max) {
            $this->setError($flag, $error, $errno, 'length');
        }
        $this->save();
        return $this;
    }

    /**
     * 验证相等
     * @param $val
     * @param bool $flag
     * @param null $error
     * @param null $errno
     * @return $this
     */
    public function equal($val, $flag = true, $error = null, $errno = null)
    {
        if ($this->_val !== $val) {
            $this->setError($flag, $error, $errno, 'equal');
        }
        $this->save();
        return $this;
    }

    /**
     * xss过滤
     * @return $this
     */
    public function xss()
    {
        $this->_val = $this->filterXSS($this->_val);
        $this->save();
        return $this;
    }

    /**
     * Y-m-d H:i:s 2011-02-09 05:03:06
     * Y-n-j G:i:s 2011-2-9 5:03:06
     * 验证输入的日期格式是否合法,flag = 'now' 可以按format 得到当前日期默认值
     */
    public function date($flag = true, $format = 'Y-m-d H:i:s', $error = true, $errno = null)
    {
        $pa = str_replace(array('Y', 'm', 'd', 'H', 'i', 's', 'n', 'j', 'G'),
            array('([0-9]{4})', '([0-9]{2})', '([0-9]{2})', '([0-9]{2})', '([0-9]{2})', '([0-9]{2})', '([0-9]{1,2})', '([0-9]{1,2})', '([0-9]{1,2})'), $format);
        $pa = "/^{$pa}$/";
        if (preg_match($pa, $this->_val) === 0) {
            $this->setError($flag, $error, $errno, 'date');
        }
        $this->save();
        return $this;
    }


    public function setError($flag, $error, $errno, $systpl = null)
    {
        $this->_val = null;
        if (empty($error)) {
            $tpl = $GLOBALS['ant']['antr_error'][$systpl];
        } else {
            $tpl = $error;
        }
        $name = $this->_name;
        $value = $this->_val;
        $error = eval('return "' . $tpl . '";');
        $e = array(
            'error' => $error, 'errno' => $errno
        );

        if ($flag == false) {
            if (!isset($this->warning[$this->_type])) {
                $this->warning[$this->_type] = new ante();
            }
            $this->warning[$this->_type]->setError($error, $errno, $this->_key);
        } else {
            $this->failed = true;
            if (!isset($this->error[$this->_type])) {
                $this->error[$this->_type] = new ante();
            }
            $this->error[$this->_type]->setError($error, $errno, $this->_key);
        }
        if ($flag == true && $this->find_all_errors == false) {
            throw new Exception($error, $errno);
        }
    }

    /**
     * @param null $type
     * @return ante
     */
    public function getErrors($type = null)
    {
        if ($type == null)
            return $this->error;
        else
            return $this->error[$type];
    }

    public function getWarnings($type = null)
    {
        if ($type == null)
            return $this->warning;
        else
            return $this->warning[$type];
    }

    public function getErrorString()
    {
        if (isset($this->error[$this->_type])) {
            /**
             * @var ante $e
             */
            $e = $this->error[$this->_type];
            return $e->formatErrorStack('html', false);
        }
        return '';
    }

    public function value()
    {
        return $this->getData($this->_type, $this->_key);
    }

    public function setDefault($def)
    {
        $v = $this->getData($this->_type, $this->_key);
        if ($v === null) {
            $this->_val = $def;
        }
        $this->save();
        return $this;
    }

    public function setValue($value)
    {
        $this->_val = $value;
        return $this;
    }

    public function filterXSS($str)
    {
        return preg_replace('/[\:\<\>\!\[\]\{\}\(\)\;\\\]/i', '', $str);
    }

    /**
     * 使用系统自带的函数做一些过滤
     * @param $f
     * @param $args
     * @return $this
     */
    public function __call($f, $args)
    {
        if (function_exists($f) && $this->_val !== null) {
            if (is_array($args)) array_unshift($args, $this->_val);
            else
                $args = array($this->_val);
            $this->_val = call_user_func_array($f, $args);
        }
        return $this;
    }

    public function run()
    {
        try {
            $this->exec();
        } catch (Exception $e) {
            if (defined('DEBUG')) {
                echo $this->getErrorString();
                die;
            }
            return false;
        }

        return !$this->failed;
    }

    public function getValue($key)
    {
        return $this->getData('get', $key);
    }

    public function postValue($key)
    {
        return $this->getData('post', $key);
    }

    public function cookieValue($key)
    {
        return $this->getData('cookie', $key);
    }

    protected function getData($type, $key)
    {
        if (!isset($this->_data[$type][$key]))
            return null;
        return $this->_data[$type][$key][0];
    }

    protected function getName($type, $key)
    {
        if (!isset($this->_data[$type][$key]))
            return null;
        return $this->_data[$type][$key][1];
    }

    public function trim()
    {
        $this->_val = trim($this->_val);
        $this->save();
        return $this;
    }

    /**
     * @param  $type
     * @param  $key
     * @return antr
     */
    public function keep($type, $key)
    {
        $this->_type = $type;
        $this->_key = $key;
        $this->_val = $this->getData($type, $key);
        $this->_name = $this->getName($type, $key);
        return $this;
    }

    public static function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

}

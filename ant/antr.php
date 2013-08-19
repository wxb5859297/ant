<?php
/**
 * antr Ant �ı�׼���,���ڱ�д�������
 * ������ȫ������һ����,����������ant��
 * ÿһ�����˺�������������
 * 1���Ƿ��Ա���ģ�����Ĺ��˻ᵼ���쳣�׳�����ִ�н��������κδ��룬��������һ��������Ϣ��
 * ���Ǳ���Ĳ��ԣ����ʧ�ܣ����Ը���һ��Ĭ��ֵ����null�����ǲ��ᵼ���׳��쳣������һ��������Ϣ
 * 2����Ϣģ�壬��������й��˺������Լ���ģ�壬��������Ȼ�����Լ����ô�����Ϣģ��
 * �÷�
 * �ڲ�ͬ�ű���ʹ��(ant �е��÷�)
 * $r->post('cid')->int(false,0)->save();//������벻��һ����������0��Ĭ��ֵ
 * echo $r->post('cid')->value();
 * ֱ�ӻ�ȡ������ֵ
 * $r->post('cid')->int(false,0)->save()->value();
 * ��ȡ��������
 * $r->post()->getErrorString();
 *
 * ע�⣺������Ϻ�ֵ�������save,�κ�һ��������Ӧ����(deal,get,post,cookie)���������ú�ʹ��
 *
 * @example          $r->get('cid')->int(false,0)->save();$r->value('cid','get');//0
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

        $this->_data[$this->_type][$this->_key] = array($this->_val,$this->_name);
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

    //==== ���������� ============================================================================ 
    /**
     * �ж�һ�������Ƿ���int���ͣ��������ַ���int
     * @return antr
     */
    public function int($flag = true, $error = null, $errno = null)
    {
        if (!is_int($this->_val) && preg_match('/^\d+$/', $this->_val) === 0) {
            $this->setError($flag, $error, $errno, 'int');
        }
        $this->save();
        return $this;
    }

    /**
     * ��֤�����룬�������ַ����Ⱥ�δ���õ��������
     * @return antr
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
     * ��֤��������
     * @return antr
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
     * ��֤�ַ�������,ascii
     * @return antr
     *
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
     * ��֤���
     * @return antr
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
     * ����
     * @return antr
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
     * ��֤��������ڸ�ʽ�Ƿ�Ϸ�,flag = 'now' ���԰�format �õ���ǰ����Ĭ��ֵ
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

    //==== ���������� END============================================================================


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
            //print_r(get_class_methods($this->error[$this->_type]));die;
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
            return $e->formatErrorStack('html',false);
        }
        return '';
    }

    public function value()
    {
        return $this->getData($this->_type,$this->_key);
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

    /**
     * ����һ��ֵ������������
     */
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
     * ʹ��ϵͳ�Դ��ĺ�����һЩ����
     * @return antr
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
        $this->_name = $this->getName($type,$key);
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

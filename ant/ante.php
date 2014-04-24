<?php
/**
 * 简易的错误捕获
 */
class ante
{
    public function __construct(&$errorStack = array())
    {
        $this->es = & $errorStack;
    }

    public function getError($key = null)
    {
        $e = $this->getErrorInfo($key);
        if ($e)
            return $e['error'];
        return false;
    }

    public function setError($error, $errno = '', $key = null)
    {
        if ($key == null) {
            $this->es[] = array('error' => $error, 'errno' => $errno);
        } else {
            $this->es[$key] = array('error' => $error, 'errno' => $errno);
        }
    }

    public function &getErrorInfo($key = null)
    {
        $flag = false;
        if (empty($this->es)) return $flag;
        if ($key == null)
            return $this->es[count($this->es) - 1];
        else
            return $this->es[$key];
    }

    public function getErrorStack()
    {
        return $this->es;
    }

    public function formatErrorStack($type = 'html', $withKey = true)
    {
        $w = "\n";
        $s = '';
        foreach ($this->es as $k => $e) {
            if ($e['errno']) {
                $s = "Errno:{$e['errno']} - {$e['error']}{$w}" . $s;
            } else {
                $s = "{$e['error']}{$w}" . $s;
            }

            if ($withKey) {
                $s = "[ $k ] " . $s;
            }
        }

        if ($type == 'html') {
            $s = htmlspecialchars($s);
            return str_replace("\n", '<br/>', $s);
        }

        return $s;
    }

    public function __toString()
    {
        return $this->formatErrorStack();
    }

    public function printErrorStack()
    {
        antp::info('wrong', 'Ant 内部错误', '输出错误栈', $this->formatErrorStack('none'));
    }
}

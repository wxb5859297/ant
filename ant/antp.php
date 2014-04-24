<?php
class antp
{
    public $act;
    public $rs;
    public $params;

    public function __construct($rs, $act)
    {
        $this->init($rs, $act);
    }

    public function init($rs, $act)
    {
        $this->rs = $rs;
        $this->act = $act;
    }

    public function loadData($params)
    {
        $this->params = $params;
    }

    public function sDisplay()
    {
        ob_start();
        $this->loadTpl();
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }

    public function loadTpl()
    {
        if (!antl::getInstance()->load('tpl', $this->rs, $this->act, $this->params)) {
            ant::E('TEMPLETE_NOT_FOUND', array('rs' => $this->rs, 'act' => $this->act));
            return false;
        }
        return true;
    }

    public function display()
    {
        return $this->loadTpl();
    }

    public static function JS($fn)
    {
        echo '<script type="text/javascript" src="' . $fn . '" ></script>' . "\n";
    }

    public static function CSS($fn)
    {
        echo '<link rel="stylesheet" type="text/css" href="' . $fn . '" />';
    }

    public static function info($type = "wrong", $title = "Ant 内部错误", $info = "", $detail = "")
    {
        include(PATH_ANT . 'anttpl/info.php');
    }
}

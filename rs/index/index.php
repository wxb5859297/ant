<?php
/**
 * index demo
 * Class rs_index_index
 */

class rs_index_index extends antc
{
    public function exec(antr $r)
    {
        $this->noView(); //不加载视图，禁止使用die、exit，强制退出
        echo 'hello world!';
    }
}
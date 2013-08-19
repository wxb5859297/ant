<?php
/**数据源db类
 *
 * @author wuxiabing
 * @date 12-5-11 晚上10:30
 */

class lib_datasource_db extends lib_datasource_abstract
{
    private $support = array('mysql', 'oracle');

    public function getDb($type = 'mysql', $driver = 'mysqli')
    {
    }

    public function getDataSource($type, $source)
    {
    }
}
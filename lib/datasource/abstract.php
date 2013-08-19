<?php
/**数据源抽象类
 *
 * @author wuxiabing
 * @date 12-5-11 晚上10:30
 */

abstract class lib_datasource_abstract
{
    private function checkSupport()
    {
    }

    abstract public function getDataSource($type, $source);
}
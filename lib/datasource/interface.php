<?php
/**
 * 数据源抽象类
 *
 * @author wuxiabing
 * @date 12-5-11 晚上10:30
 */

interface lib_datasource_interface
{
    public function getDataSource($type, $source);
}

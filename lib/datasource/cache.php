<?php
/**数据源cache类
 *
 * @author wuxiabing
 * @date 12-5-11 晚上10:30
 */

class lib_datasource_cache extends lib_datasource_abstract
{
    private $support = array('redis', 'memcache');

    public function getCache($type = 'redis')
    {
    }

    public function getDataSource($type, $source)
    {
    }
}
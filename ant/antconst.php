<?php
/**
 * the globals of ant framework
 * ֮����ֹʹ��globals����Ϊȫ�ֱ���̫������
 */
$GLOBALS['ant'] = array();
$GLOBALS['ant']['core_error'] = array(
    'TEMPLETE_NOT_FOUND'=>array(
        'title'    =>'ģ��δ�ҵ�',
        'detail'   =>'����·����$path_view{$rs}/{$act}'
    ),
    'CONTROLLER_NOT_FOUND'=>array(
        'title'    =>'������δ�ҵ�',
        'detail'   =>'����·����$path_rs{$rs}/{$act}'
    )
);
$GLOBALS['ant']['antr_error'] = array(
    'number'       => '{$name} ��Ҫ����һ�����֣������������({$value})',
    'int'          => '{$name} ��Ҫһ������,���������� \'{$value}\'',
    'isEmpty'      => '{$name} ����Ϊ��',
    'equal'        => '{$name} ������ֵ��ָ��ֵ�����',
    'length'       => '{$name} �ĳ��Ȳ�����Ҫ��'
);

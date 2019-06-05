<?php

return array(
    'default' => 'exchange',

    //redis资源
    'redis' => array(
        'exchange' => array(
            'write' => $_SERVER['SINASRV_REDIS1_HOST'] . ':' . $_SERVER['SINASRV_REDIS1_PORT'], //格式"host:port", 只允许一个
            'read' => $_SERVER['SINASRV_REDIS1_HOST_R'] . ':' . $_SERVER['SINASRV_REDIS1_PORT_R'],
        ),
       // 安全队列
        'exchange_security_push' => array(
            'write' => 'rm51151.eos.grid.sina.com.cn:51151',
            'read' => 'rs51151.eos.grid.sina.com.cn:51151',
        ),
        'exchange_security_pop' => array(
            'write' => 'rm51151.eos.grid.sina.com.cn:51151',
            'read' => 'rs51151.eos.grid.sina.com.cn:51151',
        ),
    ),

    //mysql资源
    'mysql' => array(
        'exchange' => array(
            'write' => array(
                'host' => $_SERVER['SINASRV_DB1_HOST'],
                'port' => $_SERVER['SINASRV_DB1_PORT'],
                'name' => $_SERVER['SINASRV_DB1_NAME'],
                'user' => $_SERVER['SINASRV_DB1_USER'],
                'pass' => $_SERVER['SINASRV_DB1_PASS'],
                'attr' => array('part' => '128'),
            ),
            'read' => array(
                'host' => $_SERVER['SINASRV_DB1_HOST_R'],
                'port' => $_SERVER['SINASRV_DB1_PORT_R'],
                'name' => $_SERVER['SINASRV_DB1_NAME_R'],
                'user' => $_SERVER['SINASRV_DB1_USER_R'],
                'pass' => $_SERVER['SINASRV_DB1_PASS_R'],
                'attr' => array('part' => '128'),
            ),
        ),
    ),
);

<?php
$sum = [
    [
        'name' => 'amount',//字段名
        'as' => 'money',//别名
        'where' => 'id=1 || id=2', //字段类型
    ]
];
$add = [
    'id' => ['name' => 'id', 'type' => 'int(11)'],//主键
    'engine' => 'InnoDB',//引擎
    'auto' => 1,//自动递增
    'charset' => 'utf8mb4',//字符集
    'comment' => 'demo table',//表单备注
    'list' => [
        'title' => [
            'type' => 'varchar(200)', //字段类型
            'charset' => 'utf8mb4', //字符集
            'key' => false, //是否键
            'null' => false, //不是null
            'default' => 0, //字段默认值
            'time' => false, //根据当前时间戳更新
            'comment' => 'demo field'//字段备注
        ],
        'content' => [
            'type' => 'blob', //字段类型
            'charset' => 'utf8mb4', //字符集
            'key' => false, //是否键
            'null' => false, //不是null
            'default' => "NULL", //字段默认值
            'time' => false, //根据当前时间戳更新
            'comment' => 'demo field'//字段备注
        ]
    ]
];
$row = [
    'field' => [
        'type' => 'int(11)', //字段类型
        'charset' => 'utf8mb4', //字符集
        'null' => false, //不是null
        'default' => 0, //字段默认值
        'time' => false, //根据当前时间戳更新
        'comment' => 'demo field'//字段备注
    ]
];
$edits = [
    'field' => [
        'name' => '',//新的名字
        'type' => 'int(11)', //字段类型
        'default' => 0, //字段默认值
        'comment' => 'demo field'//字段备注
    ]
];
//$config = config('plugin.back.database.connections.admin');
//$pdo = new PdoHelper($config);
//$mysqli = new MysqliHelper($config);
//$pdo->opt('base', 'back_')->driver('mysql')->export(__DIR__ . '/111_ab_data.sql');
//$mysqli->export(__DIR__ . '/111_admin.sql');
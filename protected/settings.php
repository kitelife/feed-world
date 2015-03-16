<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 17:18
 */

return array(
    'database' => array(
        'name' => 'rss_world',
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'password' => '06122553',
        'options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ),
    ),
);
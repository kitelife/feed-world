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
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ),
    ),
    'github' => array(
        'client_id' => 'ec7bea72d99faab0d330',
        'client_secret' => 'e33e9e270f6fe9bae3611f2edbaafea0917c0e5a',
        'redirect_uri' => 'http://58.215.187.122:9009/user/login',
        'scope' => '',
    ),
    'weibo' => array(

    ),
);
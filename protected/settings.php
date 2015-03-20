<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 17:18
 */

return array(
    'database' => array(
        'name' => 'feed_world',
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'password' => 'root',
        'options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ),
    ),
    'github' => array(
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => '',
        'scope' => '',
        'state' => 'github-login-feed-world'
    ),
    'weibo' => array(
        'AppKey' => '',
        'client_secret' => '',
        'redirect_uri' => '',
        'scope' => '',
        'state' => 'weibo-login-feed-world'
    ),
    'requests' => array(
        'timeout' => 20,
    ),
    'session' => array(
        'expires' => '1 year',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => false,
        'name' => 'slim_session',
        'secret' => '1qazXSW@',
    ),
);
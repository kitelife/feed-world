<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 14:52
 */

require '../vendor/autoload.php';
require './autoload.php';

use \RSSWorld\Handlers;

$app = new \Slim\Slim(require('./settings.php'));

$app->container->singleton('log', function ($c) {
    $log = new \Monolog\Logger('rss-world');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// 数据库连接
$app->container->singleton('db', function ($c) {
    $dbSettings = $c['settings']['database'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $dbSettings['host'], $dbSettings['port'], $dbSettings['name']);
    return new \PDO($dsn, $dbSettings['username'], $dbSettings['password'], $dbSettings['options']);
});

// 主页
$app->get('/', function () {
    echo '欢迎来到RSS世界！';
    return true;
});

// 新建订阅
$app->post('/subscribe', function () use ($app) {
    return Handlers::subscribeFeed($app);
});

// 取消订阅
$app->post('/unsubscribe/:id', function ($id) use ($app) {
    return Handlers::unsubscribe($app, $id);
});

// 资源(订阅)列表
$app->get('/feed/', function () {

});

// 资源的文章列表
$app->get('/feed/:id/', function ($id) {

});


$app->group('/feed/:feedID/post/:postID', function () use ($app) {
    $app->get('/', function ($feedID, $postID) {

    });

    $app->post('/', function ($feedID, $postID) {
        // read、unread、star、unstar
    });
});

$app->run();
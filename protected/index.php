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
use \RSSWorld\Helpers;

$app = new \Slim\Slim(require('./settings.php'));

$app->add(new \Slim\Middleware\SessionCookie(array(
        'expires' => '20 minutes',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => false,
        'name' => 'slim_session',
        'secret' => '1qazXSW@',
        'cipher' => MCRYPT_RIJNDAEL_256,
        'cipher_mode' => MCRYPT_MODE_CBC
    ))
);

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

/*
 * 所有登录状态检查的逻辑应放在一个中间件中去实现
 * */

// 主页
$app->get('/', function () use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        // 显示HTML

    }

    $app->response->redirect('/user/login', 302);
    return true;
});

$app->map('/user/login', function () use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        $app->response->redirect('/feed', 302);
    }
    if ($app->request->isGet()) {

    } else {
        if ($app->request->isPost()) {

        }
    }
})->via('GET', 'POST');

$app->post('/user/logout', function () use ($app) {
    unset($_SESSION['user_id']);
    $app->response->redirect('/user/login', 302);
    return true;
});

// 新建订阅
$app->post('/feed/subscribe', function () use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        Handlers\FeedHandlers::subscribeFeed($app);
        return true;
    }
    $app->response->redirect('/user/login', 302);
    return true;
});

// 取消订阅
$app->post('/feed/:id/unsubscribe', function ($id) use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        return Handlers\FeedHandlers::unsubscribe($app, $id);
    }
    $app->response->redirect('/user/login', 302);
    return true;
});

// 资源(订阅)列表
$app->get('/feed/', function () use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        Handlers\FeedHandlers::ListFeed($app);
        return true;
    }

    Helpers\ResponseUtils::responseError(Helpers\CodeStatus::REQUIRE_LOGIN);
    return true;
});

// 资源的文章列表
$app->get('/feed/:feedID/', function ($feedID) use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        Handlers\PostHandlers::ListPost($app, $feedID);
        return true;
    }
    Helpers\ResponseUtils::responseError(Helpers\CodeStatus::REQUIRE_LOGIN);
    return true;
});


$app->post('/feed/:feedID/post/:postID', function ($feedID, $postID) use ($app) {
    if (Helpers\ResponseUtils::checkLogin($app)) {
        Handlers\PostHandlers::ChangePostStatus($app, $feedID, $postID);
        return true;
    }
    Helpers\ResponseUtils::responseError(Helpers\CodeStatus::REQUIRE_LOGIN);
    return true;
});

$app->run();
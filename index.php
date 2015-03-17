<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 14:52
 */

require './vendor/autoload.php';
require './protected/autoload.php';

use \FeedWorld\Handlers;
use \FeedWorld\Helpers;

date_default_timezone_set('Asia/Shanghai');

$app = new \Slim\Slim(require('./protected/settings.php'));

// 这个中间件必须比SessionCookie先add
$app->add(new \FeedWorld\Middlewares\UserSession());

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
    $log->pushHandler(new \Monolog\Handler\StreamHandler('./logs/app.log', \Monolog\Logger::DEBUG));
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
    echo file_get_contents('./templates/index.html');
    return true;
});

$app->get('/user/login', function () use ($app) {
    $loginType = $app->request->get('type', null);
    if ($loginType === null){
        echo file_get_contents('./templates/login.html');
    } else {
        if ($loginType === 'github') {
            $app->response->redirect(\FeedWorld\Helpers\GithubAPI::genAuthorizeURL($app), 302);
        }
        if ($loginType === 'weibo') {

        }
    }
    if (Handlers\UserHandlers::userLogin($app)) {
        $app->response->redirect('/', 302);
    }
    // add flash message
    //
    $app->response->redirect('/user/login', 302);
    return true;
});

$app->post('/user/logout', function () use ($app) {
    unset($_SESSION['user_id']);
    $app->response->redirect('/user/login', 302);
    return true;
});

// 新建订阅
$app->post('/feed/subscribe', function () use ($app) {
    Handlers\FeedHandlers::subscribeFeed($app);
    return true;
});

// 取消订阅
$app->post('/feed/:id/unsubscribe', function ($id) use ($app) {
    Handlers\FeedHandlers::unsubscribe($app, $id);
    return true;
});

// 资源(订阅)列表
$app->get('/feed/', function () use ($app) {
    Handlers\FeedHandlers::listFeed($app);
    return true;
});

$app->post('/feed/:feedID/update', function ($feedID) use ($app) {
    Handlers\FeedHandlers::updateFeed($app, $feedID);
    return true;
});

// 资源的文章列表
$app->get('/feed/:feedID/', function ($feedID) use ($app) {
    Handlers\PostHandlers::listPost($app, $feedID);
    return true;
});


$app->post('/feed/:feedID/post/:postID', function ($feedID, $postID) use ($app) {
    Handlers\PostHandlers::changePostStatus($app, $feedID, $postID);
    return true;
});

$app->run();
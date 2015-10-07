<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 14:52
 */

require './vendor/autoload.php';
require './protected/autoload.php';

$appSettings = require('./protected/settings.php');

date_default_timezone_set('Asia/Shanghai');

session_cache_limiter(false);
session_set_cookie_params(strtotime($appSettings['session']['expires']) - time());
session_start();

$app = new \Slim\Slim($appSettings);

$app->add(new \FeedWorld\Middlewares\UserSession());

/*
$app->add(new \Slim\Middleware\SessionCookie(
    array_merge(array('cipher' => MCRYPT_RIJNDAEL_256, 'cipher_mode' => MCRYPT_MODE_CBC), $app->settings['session'])
));
*/

$app->container->singleton('log', function ($c) {
    $log = new \Monolog\Logger('feed-world');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('./logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// 数据库连接
$app->container->singleton('db', function ($c) {
    $dbSettings = $c['settings']['database'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $dbSettings['host'], $dbSettings['port'], $dbSettings['name']);
    return new \PDO($dsn, $dbSettings['username'], $dbSettings['password'], $dbSettings['options']);
});

//
$routeCallBackDecorator = function ($handlerCallback) use ($app) {

    $runMe = function () use ($app, $handlerCallback) {
        $myArgs = func_get_args();
        array_unshift($myArgs, $app);
        try {
            call_user_func_array($handlerCallback, $myArgs);
        } catch (\Exception $e) {
            \FeedWorld\Helpers\ResponseUtils::responseExceptionWrapper($app, $e);
        }
        return true;
    };

    return $runMe;
};

/*
 * 所有登录状态检查的逻辑应放在中间件\FeedWorld\Middlewares\UserSession中去实现
 * */

// 主页
$app->get('/', function () use ($app) {
    echo file_get_contents('./templates/index.html');
    return true;
});

// 登录
$app->get('/user/login', $routeCallBackDecorator('\FeedWorld\Handlers\UserHandlers::login'));

// 退出登录
$app->map('/user/logout', function () use ($app) {
    unset($_SESSION['user_id']);
    $app->response->redirect('/user/login', 302);
    return true;
})->via('GET', 'POST');

// 获取用户信息
$app->get('/user/profile', $routeCallBackDecorator('\FeedWorld\Handlers\UserHandlers::getUserProfile'));

// 资源(订阅)列表
$app->get('/feed', $routeCallBackDecorator('\FeedWorld\Handlers\FeedHandlers::listFeed'));

// 新建订阅
$app->post('/feed/subscribe', $routeCallBackDecorator('\FeedWorld\Handlers\FeedHandlers::subscribeFeed'));

// 取消订阅
$app->post('/feed/unsubscribe', $routeCallBackDecorator('\FeedWorld\Handlers\FeedHandlers::unsubscribe'));

// 更新某个订阅
$app->post('/feed/:feedID/update', $routeCallBackDecorator('\FeedWorld\Handlers\FeedHandlers::updateFeed'))
    ->conditions(array('feedID' => '\d+'));

// 资源的文章列表
$app->get('/feed/:feedID', $routeCallBackDecorator('\FeedWorld\Handlers\PostHandlers::listPost'))
    ->conditions(array('feedID' => '\d+'));

// 更改某篇文章的状态：设置为已读、取消已读、加星、取消加星
$app->post('/feed/:feedID/post/:postID', $routeCallBackDecorator('\FeedWorld\Handlers\PostHandlers::changePostStatus'))
    ->conditions(array('feedID' => '\d+', 'postID' => '\d+'));

// 到处订阅列表
$app->get('/feed/export', $routeCallBackDecorator('\FeedWorld\Handlers\FeedHandlers:exportFeedList'));

$app->run();
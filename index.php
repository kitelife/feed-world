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
        'expires' => '1 year',
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
    if ($loginType !== null) {
        if ($loginType === 'github') {
            $app->response->redirect(\FeedWorld\Helpers\GithubAPI::genAuthorizeURL($app->settings['github']), 302);
        }
        if ($loginType === 'weibo') {
            $app->response->redirect(\FeedWorld\Helpers\WeiboAPI::genAuthorizeURL($app->settings['weibo']), 302);
        }
    }

    $codeAfterAuthorize = $app->request->get('code', null);
    $originState = $app->request->get('state', null);

    if ($codeAfterAuthorize !== null && $originState !== null) {
        if (FeedWorld\Handlers\UserHandlers::userLogin($app, $codeAfterAuthorize, $originState)) {
            $app->response->redirect('/', 302);
        } else {
            // add flash message
            //
            $app->response->redirect('/user/login', 302);
        }
    }
    echo file_get_contents('./templates/login.html');
    return true;
});

$app->map('/user/logout', function () use ($app) {
    unset($_SESSION['user_id']);
    $app->response->redirect('/user/login', 302);
    return true;
})->via('GET', 'POST');

$app->get('/user/profile', function () use ($app) {
    FeedWorld\Helpers\ResponseUtils::responseJSON(Handlers\UserHandlers::getUserProfile($app));
    return true;
});

// 资源(订阅)列表
$app->get('/feed', function () use ($app) {
    Handlers\FeedHandlers::listFeed($app);
    return true;
});

// 新建订阅
$app->post('/feed/subscribe', function () use ($app) {
    Handlers\FeedHandlers::subscribeFeed($app);
    return true;
});

// 取消订阅
$app->post('/feed/unsubscribe', function () use ($app) {
    Handlers\FeedHandlers::unsubscribe($app);
    return true;
});

$app->post('/feed/:feedID/update', function ($feedID) use ($app) {
    Handlers\FeedHandlers::updateFeed($app, $feedID);
    return true;
})->conditions(array('feedID' => '\d+'));

// 资源的文章列表
$app->get('/feed/:feedID', function ($feedID) use ($app) {
    Handlers\PostHandlers::listPost($app, $feedID);
    return true;
})->conditions(array('feedID' => '\d+'));


$app->post('/feed/:feedID/post/:postID', function ($feedID, $postID) use ($app) {
    Handlers\PostHandlers::changePostStatus($app, $feedID, $postID);
    return true;
})->conditions(array('feedID' => '\d+', 'postID' => '\d+'));

$app->run();
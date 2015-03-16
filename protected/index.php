<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 14:52
 */

require '../vendor/autoload.php';
require './autoload.php';

use \RSSWorld\Helpers;

$app = new \Slim\Slim(array());

$app->container->singleton('log', function ($c) {
    $log = new \Monolog\Logger('rss-world');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// 主页
$app->get('/', function () {
    echo '欢迎来到RSS世界！';
    return true;
});

// 新建订阅
$app->post('/subscribe', function () use ($app) {
    $targetURL = $app->request->post('url', '');
    if ($targetURL === '') {
        Helpers\ResponseUtils::responseError(Helpers\CodeStatus::PARAMETER_NOT_EXISTED);
        return true;
    }

    $targetURL = trim($targetURL);
    if (strpos($targetURL, 'http://') !== 0 && strpos($targetURL, 'https') !== 0) {
        $targetURL = 'http://' . $targetURL;
    }

    $targetURLResponse = Requests::get($targetURL);
    if (!$targetURLResponse->success) {
        Helpers\ResponseUtils::responseError(Helpers\CodeStatus::RESOURCE_NOT_ACCESSIBLE);
        return true;
    }

    $resourceData = new SimpleXMLElement($targetURLResponse->body, LIBXML_NOWARNING | LIBXML_NOERROR);

    $resourceType = 'rss';
    if (in_array('http://www.w3.org/2005/Atom', $resourceData->getDocNamespaces(), true)) {
        $resourceType = 'atom';
    } else {
        if (!$resourceData->channel) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::NOT_VALID_RESOURCE);
            return true;
        }
    }

    Helpers\ResponseUtils::responseJSON($resourceData);

    return true;
});

// 取消订阅
$app->post('/unsubscribe/:id', function ($id) {

});

// 资源(订阅)列表
$app->get('/resource/', function () {

});

// 资源的文章列表
$app->get('/resource/:id/post', function ($id) {

});


$app->group('/resource/:resourceID/post/:postID', function () use ($app) {
    $app->get('/', function ($resourceID, $postID) {

    });

    $app->post('/', function ($resourceID, $postID) {
        // read、unread、star、unstar
    });
});

$app->run();
<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/17
 * Time: 14:46
 */

namespace FeedWorld\Middlewares;

class UserSession extends \Slim\Middleware
{
    public function call()
    {
        $app = $this->app;
        $app->log->debug($app->request->getPath());
        if (\FeedWorld\Helpers\CommonUtils::checkLogin($app) === false
            && strcmp($app->request->getPath(), '/user/login') !== 0
        ) {
            if ($app->request->isAjax()) {
                \FeedWorld\Helpers\ResponseUtils::responseError(\FeedWorld\Helpers\CodeStatus::REQUIRE_LOGIN);
                return false;
            }
            $this->app->response->redirect('/user/login', 302);
        }
        $this->next->call();
        return true;
    }
}
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

        $hasLogin = \FeedWorld\Helpers\CommonUtils::checkLogin($app);
        $toLogin = strpos($app->request->getPathInfo(), '/user/login') !== 0 ? false : true;

        if (!$hasLogin &&  !$toLogin) {
            if ($app->request->isAjax()) {
                \FeedWorld\Helpers\ResponseUtils::responseError(\FeedWorld\Helpers\CodeStatus::REQUIRE_LOGIN);
                return false;
            }
            $this->app->response->redirect('/user/login', 302);
        }

        if ($hasLogin && $toLogin) {
            $this->app->response->redirect('/', 302);
        }
        $this->next->call();
        return true;
    }
}
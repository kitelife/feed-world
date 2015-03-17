<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/17
 * Time: 14:46
 */

namespace RSSWorld\Middlwares;

class UserSession extends \Slim\Middleware
{
    public function call()
    {
        $app = $this->app;
        if (\RSSWorld\Helpers\ResponseUtils::checkLogin($app) === false
            || strcmp($app->request->getScriptName(), '/user/login') !== 0
        ) {
            if ($app->request->isAjax()) {
                \RSSWorld\Helpers\ResponseUtils::responseError(\RSSWorld\Helpers\CodeStatus::REQUIRE_LOGIN);
                return false;
            }
            $this->app->response->redirect('/user/login', 302);
        }
        $this->next->call();
        return true;
    }
}
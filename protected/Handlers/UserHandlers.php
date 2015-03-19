<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/17
 * Time: 13:12
 */

namespace FeedWorld\Handlers;


class UserHandlers
{

    protected static function newUser($app, $userInfo)
    {
        $insertNewUser = 'INSERT INTO user (`from_where`, `id_from`, `name_from`) VALUES (:from_where, :id_from, :name_from)';
        $stmt = $app->db->prepare($insertNewUser);
        $stmt->execute(array(
            ':from_where' => $userInfo['from_where'],
            ':id_from' => $userInfo['id_from'],
            ':name_from' => $userInfo['name_from']
        ));
        return $app->db->lastInsertId();
    }

    public static function userLogin($app, $codeAfterAuthorize, $originState)
    {
        $appSettings = $app->settings;
        if ($originState === $appSettings['github']['state']) {
            $res = \FeedWorld\Helpers\GithubAPI::fetchAccessToken($codeAfterAuthorize, $appSettings);
            if ($res === null) {
                // throw new \Exception('连接不上Github，登陆失败！', 500);
                return false;
            }
            $userProfile = \FeedWorld\Helpers\GithubAPI::fetchUserProfile($res['access_token'], $appSettings);
            if ($userProfile === null) {
                return false;
            }

            $checkUserExist = 'SELECT user_id FROM user WHERE from_where="github" AND id_from = :id_from';
            $stmt = $app->db->prepare($checkUserExist);
            $stmt->execute(array(':id_from' => $userProfile['id']));
            $oneRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (empty($oneRow)) {
                $userID = self::newUser($app, array(
                    'from_where' => 'github',
                    'id_from' => $userProfile['id'],
                    'name_from' => $userProfile['login'],
                ));
            } else {
                $userID = $oneRow['user_id'];
            }
            $_SESSION['user_id'] = $userID;
            return true;
        }

        if ($originState === $appSettings['weibo']['state']) {
            $res = \FeedWorld\Helpers\WeiboAPI::fetchAccessToken($codeAfterAuthorize, $appSettings);
            if ($res === null) {
                return false;
            }
            $idFrom = $res['uid'];
            $requestParams = array(
                'source' => $appSettings['weibo']['AppKey'],
                'access_token' => $res['access_token'],
                'uid' => $idFrom,
            );
            $userProfile = \FeedWorld\Helpers\WeiboAPI::fetchUserProfile($requestParams, $appSettings);
            if ($userProfile === null) {
                return false;
            }
            $checkUserExist = 'SELECT user_id FROM user WHERE from_where="weibo" AND id_from = :id_from';
            $stmt = $app->db->prepare($checkUserExist);
            $stmt->execute(array(':id_from' => $idFrom));
            $oneRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (empty($oneRow)) {
                $userID = self::newUser($app, array(
                    'from_where' => 'weibo',
                    'id_from' => $idFrom,
                    'name_from' => $userProfile['name']
                ));
            } else {
                $userID = $oneRow['user_id'];
            }
            $_SESSION['user_id'] = $userID;
            return true;
        }
        return false;
    }

    public static function getUserProfile($app)
    {
        $selectUserProfile = 'SELECT * FROM user WHERE user_id=:user_id';
        $stmt = $app->db->prepare($selectUserProfile);
        $stmt->execute(array(':user_id' => $_SESSION['user_id']));
        $myProfile = $stmt->fetch(\PDO::FETCH_ASSOC);
        \FeedWorld\Helpers\ResponseUtils::responseJSON($myProfile);
    }
}
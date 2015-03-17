<?php
/**
 * Created by PhpStorm.
 * User: xiayf
 * Date: 3/17/15
 * Time: 9:22 PM
 */

namespace FeedWorld\Helpers;


class GithubAPI {

    public static $githubConfig = null;

    public static function genAuthorizeURL($app) {
        self::$githubConfig = $app->settings['github'];
        $queryString = http_build_query(array(
            'client_id' => self::$githubConfig['client_id'],
            'redirect_uri' => self::$githubConfig['redirect_uri'],
            'scope' => self::$githubConfig['scope'],
        ));
       return sprintf('https://github.com/login/oauth/authorize?%s', $queryString);
    }

    public static function fetchAccessToken($githubCode) {
        $payLoad = array(
            'client_id' => self::$githubConfig['client_id'],
            'client_secret' => self::$githubConfig['client_secret'],
            'code' => $githubCode,
            'redirect_uri' => self::$githubConfig['redirect_uri'],
        );
        $header = array(
            'Accept' => 'application/json'
        );
        $resp = \Requests::post('https://github.com/login/oauth/access_token', $header, $payLoad);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body);
    }

    public static function fetchUserProfile($accessToken) {
        $resourceURL = sprintf('https://api.github.com/user?access_token=%s', $accessToken);
        $resp = \Requests::get($resourceURL);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body);
    }
} 
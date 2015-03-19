<?php
/**
 * Created by PhpStorm.
 * User: xiayf
 * Date: 3/19/15
 * Time: 9:21 PM
 */

namespace FeedWorld\Helpers;


class WeiboAPI {

    public static function genAuthorizeURL($weiboConfig) {
        $queryString = http_build_query(array(
            'client_id' => $weiboConfig['AppKey'],
            'redirect_uri' => $weiboConfig['redirect_uri'],
            'scope' => $weiboConfig['scope'],
            'state' => $weiboConfig['state']
        ));
        return sprintf('https://api.weibo.com/oauth2/authorize?%s', $queryString);
    }

    public static function fetchAccessToken($codeAfterAuthorize, $appSettings) {
        $weiboConfig = $appSettings['weibo'];
        $payLoad = array(
            'client_id' => $weiboConfig['AppKey'],
            'client_secret' => $weiboConfig['client_secret'],
            'grant_type' => 'authorization_code',
            'code' => $codeAfterAuthorize,
            'redirect_uri' => $weiboConfig['redirect_uri'],
        );
        $header = array(
            'Accept' => 'application/json'
        );
        $resp = \Requests::post('https://api.weibo.com/oauth2/access_token', $header, $payLoad, $appSettings['requests']);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body, true);
    }

    public static function fetchUserProfile($requestParams, $appSettings) {
        $queryString = http_build_query($requestParams);
        $resourceURL = sprintf('https://api.weibo.com/2/users/show.json?%s', $queryString);
        $resp = \Requests::get($resourceURL, array(), $appSettings['requests']);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body, true);
    }
} 
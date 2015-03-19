<?php
/**
 * Created by PhpStorm.
 * User: xiayf
 * Date: 3/17/15
 * Time: 9:22 PM
 */

namespace FeedWorld\Helpers;


class GithubAPI {

    public static function genAuthorizeURL($githubConfig) {
        $queryString = http_build_query(array(
            'client_id' => $githubConfig['client_id'],
            'redirect_uri' => $githubConfig['redirect_uri'],
            'scope' => $githubConfig['scope'],
            'state' => $githubConfig['state']
        ));
       return sprintf('https://github.com/login/oauth/authorize?%s', $queryString);
    }

    public static function fetchAccessToken($githubCode, $settings) {
        $githubConfig = $settings['github'];
        $payLoad = array(
            'client_id' => $githubConfig['client_id'],
            'client_secret' => $githubConfig['client_secret'],
            'code' => $githubCode,
            'redirect_uri' => $githubConfig['redirect_uri'],
        );
        $header = array(
            'Accept' => 'application/json'
        );
        $resp = \Requests::post('https://github.com/login/oauth/access_token', $header, $payLoad, $settings['requests']);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body, true);
    }

    public static function fetchUserProfile($accessToken, $settings) {
        $resourceURL = sprintf('https://api.github.com/user?access_token=%s', $accessToken);
        $resp = \Requests::get($resourceURL, array(), $settings['requests']);
        if (!$resp->success) {
            return null;
        }
        return json_decode($resp->body, true);
    }
} 
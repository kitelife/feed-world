<?php
/**
 * Created by PhpStorm.
 * User: xiayf
 * Date: 3/17/15
 * Time: 9:22 PM
 */

namespace FeedWorld\Helpers;


class GithubAPI {

    public static function genAuthorizeURL($app) {
        $githubConfig = $app->settings['github'];
        $queryString = http_build_query(array(
            'client_id' => $githubConfig['client_id'],
            'redirect_uri' => $githubConfig['redirect_uri'],
            'scope' => $githubConfig['scope'],
        ));
       return sprintf('https://github.com/login/oauth/authorize?%s', $queryString);
    }
} 
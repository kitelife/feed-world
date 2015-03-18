<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 15:30
 */

namespace FeedWorld\Helpers;

class ResponseUtils
{

    public static function responseError($errCode, $errMsg = null)
    {
        if ($errMsg === null) {
            $errMsg = CodeStatus::$statusCode[$errCode];
        }
        echo json_encode(array(
            'code' => $errCode,
            'message' => $errMsg,
        ));

        return true;
    }

    public static function responseJSON($data)
    {
        echo json_encode(array(
            'code' => CodeStatus::OK,
            'data' => $data,
        ));

        return true;
    }
}
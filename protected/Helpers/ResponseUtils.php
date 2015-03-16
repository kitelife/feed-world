<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 15:30
 */

namespace RSSWorld\Helpers;

class ResponseUtils
{

    public static function responseError($errCode)
    {
        echo json_encode(array(
            'code' => $errCode,
            'message' => CodeStatus::$statusCode[$errCode],
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
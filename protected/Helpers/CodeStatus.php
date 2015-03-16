<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 15:38
 */

namespace RSSWorld\Helpers;

class CodeStatus
{

    const OK = 1000; // 成功
    const PARAMETER_NOT_EXISTED = 1001; // 缺少必要的请求参数
    const RESOURCE_NOT_ACCESSIBLE = 1002; // 资源不可访问
    const NOT_VALID_RESOURCE = 1003; // 无效资源

    public static $statusCode = array(
        self::OK => '成功',
        self::PARAMETER_NOT_EXISTED => '缺少必要的请求参数',
        self::RESOURCE_NOT_ACCESSIBLE => '资源不可访问',
    );
}
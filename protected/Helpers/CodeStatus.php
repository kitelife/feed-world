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
    const SYSTEM_ERROR = 1004; // 系统错误
    const FEED_EXISTED = 1005; // feed已存在
    const WRONG_PARAMETER = 1006; // 参数错误
    const REQUIRE_LOGIN = 1007; // 请先登录

    public static $statusCode = array(
        self::OK => '成功',
        self::PARAMETER_NOT_EXISTED => '缺少必要的请求参数',
        self::RESOURCE_NOT_ACCESSIBLE => '资源不可访问',
        self::NOT_VALID_RESOURCE => '无效资源',
        self::SYSTEM_ERROR => '系统错误',
        self::FEED_EXISTED => 'Feed已存在',
        self::WRONG_PARAMETER => '请求参数错误',
        self::REQUIRE_LOGIN => '请先登录',
    );
}
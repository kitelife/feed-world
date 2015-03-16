<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 17:04
 */

// 注册应用自己的autoload
spl_autoload_register(function ($className) {
    $prefix = 'RSSWorld\\';
    $baseDir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }
    $relativeClassName = substr($className, $len);
    $targetFile = $baseDir . str_replace('\\', '/', $relativeClassName) . '.php';
    if (file_exists($targetFile)) {
        require $targetFile;
    }
});
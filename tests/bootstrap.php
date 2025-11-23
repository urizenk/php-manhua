<?php
/**
 * PHPUnit测试引导文件
 */

// 定义项目根目录
define('APP_PATH', dirname(__DIR__));

// 加载配置（测试环境）
$config = require APP_PATH . '/config/config.php';

// 覆盖为测试数据库
$config['database']['dbname'] = getenv('DB_NAME') ?: 'manhua_test';

// 自动加载器
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class);
    $classPath = str_replace('App/', 'app/', $classPath);
    $classPath = str_replace('Tests/', 'tests/', $classPath);
    $file = APP_PATH . '/' . $classPath . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 返回配置供测试使用
return $config;

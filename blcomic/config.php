<?php
// 数据库配置
$db_host = 'localhost';
$db_name = 'blmh_site';
$db_user = 'blmh_site';
$db_pass = '4BtsZWFSQJNHABRc';

// Redis配置
$redis_host = '127.0.0.1';
$redis_port = 6379;
$redis_pass = ''; // 如果有密码则填写

// 初始化Redis连接
try {
    $redis = new Redis();
    $redis->connect($redis_host, $redis_port);
    if (!empty($redis_pass)) {
        $redis->auth($redis_pass);
    }
    // 测试连接
    $redis->ping();
} catch (Exception $e) {
    // Redis连接失败时降级到直接查数据库
    $redis = null;
    error_log("Redis连接失败: " . $e->getMessage());
}

// 初始化数据库连接
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>
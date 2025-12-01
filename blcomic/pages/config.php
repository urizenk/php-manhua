<?php
// 数据库配置
$db_host = 'localhost';
$db_name = 'dailycode';
$db_user = 'dailycode';
$db_pass = 'JEHfnkZD6GtSdSnR';

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
} catch (Exception $e) {
    // Redis连接失败时降级到直接查数据库
    $redis = null;
}

// 初始化数据库连接（保持不变）
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>
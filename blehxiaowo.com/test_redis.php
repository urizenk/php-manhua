<?php
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
    echo "Redis连接成功！";
} catch (Exception $e) {
    echo "Redis连接失败: " . $e->getMessage();
}
?>
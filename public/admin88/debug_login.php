<?php
// 临时调试登录页面
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>调试</title></head><body>";
echo "<h1>调试信息</h1>";

try {
    $loginFile = dirname(dirname(__DIR__)) . '/views/admin/login_standalone.php';
    echo "<p>文件路径: $loginFile</p>";
    echo "<p>文件存在: " . (file_exists($loginFile) ? '是' : '否') . "</p>";
    
    if (file_exists($loginFile)) {
        echo "<p>开始包含文件...</p>";
        include $loginFile;
        echo "<p>文件包含成功</p>";
    } else {
        echo "<p style='color:red;'>错误：文件不存在！</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>异常：" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";

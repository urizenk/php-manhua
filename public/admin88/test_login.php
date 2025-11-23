<?php
/**
 * 登录页面诊断脚本
 */

// 开启错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>登录诊断</title></head><body>";
echo "<h1>登录页面诊断</h1>";

// 1. 检查PHP版本
echo "<h2>1. PHP版本</h2>";
echo "<p>PHP版本: " . phpversion() . "</p>";

// 2. 检查Session
echo "<h2>2. Session测试</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['test'] = 'success';
    echo "<p style='color:green;'>✅ Session正常</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Session错误: " . $e->getMessage() . "</p>";
}

// 3. 检查CSRF Token生成
echo "<h2>3. CSRF Token生成</h2>";
try {
    $token = bin2hex(random_bytes(32));
    echo "<p style='color:green;'>✅ Token生成成功: " . substr($token, 0, 20) . "...</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Token生成失败: " . $e->getMessage() . "</p>";
}

// 4. 检查登录视图文件
echo "<h2>4. 登录视图文件</h2>";
$loginFile = dirname(dirname(__DIR__)) . '/views/admin/login.php';
if (file_exists($loginFile)) {
    echo "<p style='color:green;'>✅ 文件存在: $loginFile</p>";
    echo "<p>文件大小: " . filesize($loginFile) . " 字节</p>";
    echo "<p>可读: " . (is_readable($loginFile) ? '是' : '否') . "</p>";
} else {
    echo "<p style='color:red;'>❌ 文件不存在: $loginFile</p>";
}

// 5. 尝试包含登录页面
echo "<h2>5. 包含登录页面测试</h2>";
echo "<div style='border:2px solid #ccc; padding:20px; margin:20px 0;'>";
try {
    ob_start();
    include $loginFile;
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "<p style='color:green;'>✅ 页面渲染成功，输出长度: " . strlen($output) . " 字符</p>";
        echo "<details><summary>点击查看输出内容</summary>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
        echo "</details>";
    } else {
        echo "<p style='color:red;'>❌ 页面渲染失败，无输出</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ 包含文件错误: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 6. 检查路由
echo "<h2>6. 路由配置</h2>";
$indexFile = __DIR__ . '/index.php';
if (file_exists($indexFile)) {
    echo "<p style='color:green;'>✅ 后台入口文件存在</p>";
} else {
    echo "<p style='color:red;'>❌ 后台入口文件不存在</p>";
}

echo "<hr>";
echo "<p><a href='/admin88/login'>返回登录页面</a></p>";
echo "</body></html>";

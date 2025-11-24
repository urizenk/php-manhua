<?php
/**
 * 登录处理 - 独立文件
 */

// 定义应用根目录
define('APP_PATH', dirname(dirname(__DIR__)));

// 加载配置
$config = require APP_PATH . '/config/config.php';

// 自动加载类
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class);
    $classPath = str_replace('App/', 'app/', $classPath);
    $file = APP_PATH . '/' . $classPath . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Session;
use App\Core\Database;

// 初始化数据库和Session
$db = Database::getInstance($config['database']);
$session = new Session($config['session']);
$session->setDatabase($db);

// 只处理POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin88/login.php');
    exit;
}

// CSRF Token验证
if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['login_error'] = 'CSRF验证失败，请刷新页面重试';
    header('Location: /admin88/login.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 检查登录失败次数（防暴力破解）
$failCount = $session->get('login_fail_count', 0);
$lastFailTime = $session->get('login_last_fail_time', 0);

// 如果5分钟内失败超过5次，暂时锁定
if ($failCount >= 5 && (time() - $lastFailTime) < 300) {
    $_SESSION['login_error'] = '登录失败次数过多，请5分钟后再试';
    header('Location: /admin88/login.php');
    exit;
}

// 查询管理员
$admin = $db->queryOne(
    "SELECT * FROM admins WHERE username = ?",
    [$username]
);

if ($admin && password_verify($password, $admin['password'])) {
    // 登录成功，清除失败计数
    $session->delete('login_fail_count');
    $session->delete('login_last_fail_time');
    
    // 设置登录状态
    $session->adminLogin($admin['id'], $admin['username']);
    
    // 重定向到后台首页
    header('Location: /admin88/dashboard.php');
    exit;
} else {
    // 登录失败，增加失败计数
    $session->set('login_fail_count', $failCount + 1);
    $session->set('login_last_fail_time', time());
    
    $_SESSION['login_error'] = '用户名或密码错误';
    header('Location: /admin88/login.php');
    exit;
}

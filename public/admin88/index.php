<?php
/**
 * 后台管理入口文件
 */

// 定义应用根目录
define('APP_PATH', dirname(dirname(__DIR__)));

// 加载配置
$config = require APP_PATH . '/config/config.php';

// 设置时区
date_default_timezone_set($config['app']['timezone']);

// 设置错误报告
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('APP_DEBUG', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('APP_DEBUG', false);
}

// 自动加载类
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class);
    $classPath = str_replace('App/', 'app/', $classPath);
    $file = APP_PATH . '/' . $classPath . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 加载辅助函数
require APP_PATH . '/app/helpers.php';

// 初始化核心组件
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;

try {
    // 初始化数据库
    $db = Database::getInstance($config['database']);
    
    // 初始化Session
    $session = new Session($config['session']);
    $session->setDatabase($db);
    
    // 创建路由器
    $router = new Router();
    
    // ==========================================
    // 后台路由定义
    // ==========================================
    
    // 后台首页
    $router->get('/', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        // 让视图文件能访问变量
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/dashboard.php';
        exit;
    });
    
    // 登录页面 - 重定向到独立登录文件
    $router->get('/login', function() use ($session) {
        if ($session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/'));
            exit;
        }
        // 重定向到独立的login.php文件
        Router::redirect(Router::url('/admin88/login.php'));
        exit;
    });
    
    // 登录处理
    $router->post('/login', function() use ($session, $db) {
        // CSRF Token验证
        if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'CSRF验证失败，请刷新页面重试';
            Router::redirect(Router::url('/admin88/login'));
            return;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // 检查登录失败次数（防暴力破解）
        $failCount = $session->get('login_fail_count', 0);
        $lastFailTime = $session->get('login_last_fail_time', 0);
        
        // 如果5分钟内失败超过5次，暂时锁定
        if ($failCount >= 5 && (time() - $lastFailTime) < 300) {
            $_SESSION['login_error'] = '登录失败次数过多，请5分钟后再试';
            Router::redirect(Router::url('/admin88/login'));
            return;
        }
        
        $admin = $db->queryOne(
            "SELECT * FROM admins WHERE username = ?",
            [$username]
        );
        
        if ($admin && password_verify($password, $admin['password'])) {
            // 登录成功，清除失败计数
            $session->delete('login_fail_count');
            $session->delete('login_last_fail_time');
            
            $session->adminLogin($admin['id'], $admin['username']);
            Router::redirect(Router::url('/admin88/'));
        } else {
            // 登录失败，增加失败计数
            $session->set('login_fail_count', $failCount + 1);
            $session->set('login_last_fail_time', time());
            
            $_SESSION['login_error'] = '用户名或密码错误';
            Router::redirect(Router::url('/admin88/login'));
        }
    });
    
    // 登出
    $router->get('/logout', function() use ($session) {
        $session->adminLogout();
        Router::redirect(Router::url('/admin88/login'));
    });
    
    // 添加漫画页面
    $router->get('/manga/add', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/manga_add.php';
        exit;
    });
    
    // 编辑漫画页面
    $router->get('/manga/edit', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/manga_edit.php';
        exit;
    });
    
    // 漫画列表
    $router->get('/manga/list', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/manga_list.php';
        exit;
    });
    
    // 标签管理
    $router->get('/tags', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/tags.php';
        exit;
    });
    
    // 访问码更新
    $router->get('/access-code', function() use ($session, $db, $config) {
        if (!$session->isAdminLoggedIn()) {
            Router::redirect(Router::url('/admin88/login'));
            exit;
        }
        $GLOBALS['session'] = $session;
        $GLOBALS['db'] = $db;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/admin/access_code.php';
        exit;
    });
    
    // 404页面
    $router->notFound(function() {
        require APP_PATH . '/views/errors/404.php';
    });
    
    // 执行路由
    $router->dispatch();
    
} catch (Exception $e) {
    // 错误处理
    if ($config['app']['debug']) {
        echo '<h1>Error</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        echo '<h1>系统错误</h1>';
        echo '<p>抱歉，系统出现了一些问题，请稍后再试。</p>';
    }
    
    // 记录错误日志
    error_log('[Admin Error] ' . $e->getMessage());
}



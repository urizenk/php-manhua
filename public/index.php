<?php
/**
 * 前台入口文件
 */

// 定义应用根目录
define('APP_PATH', dirname(__DIR__));

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
    // 前台路由定义
    // ==========================================
    
    // 首页（主界面）
    $router->get('/', function() use ($config) {
        require APP_PATH . '/views/frontend/index.php';
    });
    
    // 访问码验证
    $router->post('/verify-code', function() use ($session, $db) {
        header('Content-Type: application/json');
        
        // 检查失败次数（防暴力破解）
        $failCount = $session->get('verify_fail_count', 0);
        $lastFailTime = $session->get('verify_last_fail_time', 0);
        
        // 如果5分钟内失败超过5次，暂时锁定
        if ($failCount >= 5 && (time() - $lastFailTime) < 300) {
            echo json_encode([
                'success' => false,
                'message' => '尝试次数过多，请5分钟后再试'
            ]);
            exit;
        }
        
        $code = $_POST['code'] ?? '';
        $isValid = $session->verifyAccessCode($code, $db);
        
        if (!$isValid) {
            // 验证失败，增加失败计数
            $session->set('verify_fail_count', $failCount + 1);
            $session->set('verify_last_fail_time', time());
        } else {
            // 验证成功，清除失败计数
            $session->delete('verify_fail_count');
            $session->delete('verify_last_fail_time');
        }
        
        echo json_encode([
            'success' => $isValid,
            'message' => $isValid ? '验证成功' : '访问码错误'
        ]);
    });
    
    // 日更板块
    $router->get('/daily', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/daily.php';
    });
    
    // 韩漫合集
    $router->get('/korean', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/korean.php';
    });
    
    // 完结短漫
    $router->get('/short', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/short.php';
    });
    
    // 日漫推荐
    $router->get('/japan-recommend', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/japan_recommend.php';
    });
    
    // 日漫合集
    $router->get('/japan-collection', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/japan_collection.php';
    });
    
    // 动漫合集
    $router->get('/anime', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/anime.php';
    });
    
    // 广播剧合集
    $router->get('/drama', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/drama.php';
    });
    
    // 失效反馈
    $router->get('/feedback', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/feedback.php';
    });
    
    // 防走丢
    $router->get('/backup', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/backup.php';
    });
    
    // 详情页
    $router->get('/detail/:id', function($id) use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/detail.php';
    });
    
    // 搜索
    $router->get('/search', function() use ($session, $db, $config) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/search.php';
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
    error_log('[Frontend Error] ' . $e->getMessage());
}



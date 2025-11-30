<?php
/**
 * 前台入口文件
 * 负责初始化环境并注册前台路由
 */

// 定义应用根目录
define('APP_PATH', dirname(__DIR__));

// 加载配置
$config = require APP_PATH . '/config/config.php';

// 设置时区
date_default_timezone_set($config['app']['timezone']);

// 设置错误报告
if (!empty($config['app']['debug'])) {
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

    // 初始化 Session
    $session = new Session($config['session']);
    $session->setDatabase($db);

    // 创建路由器
    $router = new Router();

    // ==========================================
    // 前台路由定义
    // ==========================================

    // 首页（主界面）
    $router->get('/', function () use ($config, $session, $db) {
        // 让视图可以访问配置、数据库和会话
        $GLOBALS['db'] = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config'] = $config;
        require APP_PATH . '/views/frontend/index.php';
    });

    // 访问码验证
    $router->post('/verify-code', function () use ($session, $db) {
        header('Content-Type: application/json');

        // 检查失败次数（防暴力破解）
        $failCount    = $session->get('verify_fail_count', 0);
        $lastFailTime = $session->get('verify_last_fail_time', 0);

        // 如果 5 分钟内失败超过 5 次，暂时锁定
        if ($failCount >= 5 && (time() - $lastFailTime) < 300) {
            echo json_encode([
                'success' => false,
                'message' => '尝试次数过多，请 5 分钟后再试',
            ]);
            exit;
        }

        $code    = $_POST['code'] ?? '';
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
            'message' => $isValid ? '验证成功' : '访问码错误',
        ]);
    });

    // 公共函数：限制需访问码的页面
    $requireAccess = function () use ($session) {
        if (!$session->isAccessVerified()) {
            Router::redirectTo('/');
        }
    };

    // 为需要在视图中使用的全局变量做一个辅助函数
    $withGlobals = function () use ($db, $session, $config) {
        $GLOBALS['db']      = $db;
        $GLOBALS['session'] = $session;
        $GLOBALS['config']  = $config;
    };

    // 日更板块
    $router->get('/daily', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/daily.php';
    });

    // 韩漫合集
    $router->get('/korean', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/korean.php';
    });

    // 完结短漫
    $router->get('/short', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/short.php';
    });

    // 日漫推荐
    $router->get('/japan-recommend', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/japan_recommend.php';
    });

    // 日漫合集（固定内容）
    $router->get('/japan-collection', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/japan_collection.php';
    });

    // 动漫合集（固定内容）
    $router->get('/anime', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/anime.php';
    });

    // 广播剧合集（固定内容）
    $router->get('/drama', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/drama.php';
    });

    // 失效反馈（固定内容）
    $router->get('/feedback', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/feedback.php';
    });

    // 防走丢（固定内容）
    $router->get('/backup', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/backup.php';
    });

    // 通用模块路由（用于未来新增的模块类型）
    $router->get('/module/:code', function ($code) use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        // 在视图中通过 $moduleCode 获取类型编码
        $GLOBALS['moduleCode'] = $code;
        require APP_PATH . '/views/frontend/module_generic.php';
    });

    // 详情页
    $router->get('/detail/:id', function ($id) use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        // 在视图中通过 $id 使用
        $GLOBALS['id'] = $id;
        require APP_PATH . '/views/frontend/detail.php';
    });

    // 搜索
    $router->get('/search', function () use ($requireAccess, $withGlobals) {
        $requireAccess();
        $withGlobals();
        require APP_PATH . '/views/frontend/search.php';
    });

    // 404 页面
    $router->notFound(function () {
        require APP_PATH . '/views/errors/404.php';
    });

    // 执行路由
    $router->dispatch();
} catch (Exception $e) {
    // 错误处理
    if (!empty($config['app']['debug'])) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        echo '<h1>系统错误</h1>';
        echo '<p>抱歉，系统出现了一些问题，请稍后再试。</p>';
    }

    // 记录错误日志
    error_log('[Frontend Error] ' . $e->getMessage());
}


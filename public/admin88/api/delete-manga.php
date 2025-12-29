<?php
/**
 * API: 删除单个漫画
 */

define('APP_PATH', dirname(dirname(dirname(__DIR__))));

// 加载配置和自动加载
$config = require APP_PATH . '/config/config.php';

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

use App\Core\Database;
use App\Core\Session;

header('Content-Type: application/json');

try {
    $db = Database::getInstance($config['database']);
    
    // 初始化Session
    $session = new Session($config['session']);
    $session->setDatabase($db);
    
    // 验证管理员登录
    if (!$session->isAdminLoggedIn()) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        exit;
    }
    
    // CSRF Token验证
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'CSRF验证失败']);
        exit;
    }
    
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }
    
    // 获取漫画信息（删除封面图）
    $manga = $db->queryOne("SELECT cover_image FROM mangas WHERE id = ?", [$id]);
    
    // 删除漫画
    $result = $db->delete('mangas', 'id = ?', [$id]);

    if ($result !== false) {
        // 删除封面图片文件
        if ($manga && $manga['cover_image']) {
            $imagePath = APP_PATH . $manga['cover_image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // $result === 0 表示未找到（已删除/不存在）
        echo json_encode(['success' => true, 'deleted' => (int)$result]);
    } else {
        echo json_encode(['success' => false, 'message' => '删除失败']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}



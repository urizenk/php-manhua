<?php
/**
 * API: 更新漫画排序
 */

// 设置响应类型
header('Content-Type: application/json');

// 引入配置
define('APP_PATH', dirname(dirname(dirname(__DIR__))));
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/app/Core/Database.php';
require_once APP_PATH . '/app/Core/Session.php';
require_once APP_PATH . '/app/helpers.php';

$db = new \App\Core\Database($config['database']);
$session = new \App\Core\Session();

// 检查登录状态
if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

// 验证CSRF Token
if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF验证失败']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$sortOrder = (int)($_POST['sort_order'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

try {
    $result = $db->update('mangas', ['sort_order' => $sortOrder], 'id = ?', [$id]);
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => '排序更新成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失败']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '数据库错误']);
}


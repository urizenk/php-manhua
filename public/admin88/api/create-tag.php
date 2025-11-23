<?php
/**
 * API: 创建新标签
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

use App\Core\Database;

header('Content-Type: application/json');

try {
    $db = Database::getInstance($config['database']);
    
    $typeId = $_POST['type_id'] ?? 0;
    $tagName = trim($_POST['tag_name'] ?? '');
    
    if (!$typeId || !$tagName) {
        echo json_encode(['success' => false, 'message' => '参数不完整']);
        exit;
    }
    
    $tagId = $db->insert('tags', [
        'type_id' => $typeId,
        'tag_name' => $tagName,
        'tag_type' => 'category',
        'sort_order' => 0
    ]);
    
    if ($tagId) {
        echo json_encode(['success' => true, 'tag_id' => $tagId]);
    } else {
        echo json_encode(['success' => false, 'message' => '创建失败']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}



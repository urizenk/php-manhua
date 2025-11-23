<?php
/**
 * API: 获取指定类型的标签
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
    
    $typeId = $_GET['type_id'] ?? 0;
    
    if (!$typeId) {
        echo json_encode([]);
        exit;
    }
    
    $tags = $db->query(
        "SELECT id, tag_name FROM tags WHERE type_id = ? ORDER BY sort_order, id",
        [$typeId]
    );
    
    echo json_encode($tags);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}



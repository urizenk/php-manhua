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

use App\Core\Database;

header('Content-Type: application/json');

try {
    $db = Database::getInstance($config['database']);
    
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        exit;
    }
    
    // 获取漫画信息（删除封面图）
    $manga = $db->queryOne("SELECT cover_image FROM mangas WHERE id = ?", [$id]);
    
    // 删除漫画
    $result = $db->delete('mangas', 'id = ?', [$id]);
    
    if ($result) {
        // 删除封面图片文件
        if ($manga && $manga['cover_image']) {
            $imagePath = APP_PATH . $manga['cover_image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '删除失败']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}



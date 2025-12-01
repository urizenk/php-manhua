<?php
// 从数据库获取最新密码ID
function getLatestPasswordIdFromDB($pdo) {
    $stmt = $pdo->query("SELECT id FROM passwords ORDER BY id DESC LIMIT 1");
    return $stmt->fetchColumn() ?: 0;
}

// 使用Redis锁机制获取最新密码ID
function getLatestPasswordIdWithLock($pdo, $redis) {
    if (!$redis) {
        return getLatestPasswordIdFromDB($pdo);
    }

    $lock_key = 'latest_password_id_lock';
    $lock_timeout = 10; // 秒

    try {
        // 尝试获取锁
        if ($redis->setnx($lock_key, time() + $lock_timeout)) {
            $id = getLatestPasswordIdFromDB($pdo);
            $redis->setex('latest_password_id', 300, $id);
            return $id;
        } else {
            // 等待其他进程更新缓存
            usleep(100000); // 100ms
            return $redis->get('latest_password_id') ?: getLatestPasswordIdFromDB($pdo);
        }
    } finally {
        // 确保锁被释放
        if ($redis->exists($lock_key)) {
            $redis->del($lock_key);
        }
    }
}

/**
 * 上传图片文件
 */
function uploadImage($file, $type = 'covers') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // 检查文件类型
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('只允许上传 JPG, PNG, GIF, WEBP 格式的图片');
    }
    
    // 检查文件大小
    if ($file['size'] > $max_size) {
        throw new Exception('图片大小不能超过 5MB');
    }
    
    // 创建上传目录
    $upload_dir = 'uploads/' . $type . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // 生成唯一文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $filename;
    
    // 移动文件
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('图片上传失败');
    }
    
    return $upload_path;
}

/**
 * 从URL识别平台
 */
function getPlatformFromUrl($url) {
    if (strpos($url, 'baidu.com') !== false) return '百度网盘';
    if (strpos($url, 'flowus.cn') !== false) return 'Flowus';
    if (strpos($url, 'lanzov.com') !== false) return '蓝奏云';
    if (strpos($url, 'xunlei.com') !== false) return '迅雷云盘';
    if (strpos($url, 'quark.cn') !== false) return '夸克网盘';
    if (strpos($url, 'uc.cn') !== false) return 'UC网盘';
    return '其他平台';
}
?>
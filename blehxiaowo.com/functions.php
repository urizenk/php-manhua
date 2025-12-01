<?php
// 从数据库获取最新密码ID
function getLatestPasswordIdFromDB($pdo) {
    $stmt = $pdo->query("SELECT id FROM passwords ORDER BY id DESC LIMIT 1");
    return $stmt->fetchColumn();
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
?>
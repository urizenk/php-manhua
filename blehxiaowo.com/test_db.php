<?php
require 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM passwords");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($result);
} catch (PDOException $e) {
    die("查询失败: " . $e->getMessage());
}
?>
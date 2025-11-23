<?php
/**
 * 全局辅助函数
 * 提供常用的工具函数
 */

/**
 * HTML转义函数（防止XSS攻击）
 * 
 * @param string|null $string 需要转义的字符串
 * @return string 转义后的字符串
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 输出并转义（简写）
 * 
 * @param string|null $string 需要输出的字符串
 */
function echo_e($string) {
    echo e($string);
}

/**
 * 转义并保留换行符
 * 
 * @param string|null $string 需要转义的字符串
 * @return string 转义后的字符串（保留换行）
 */
function e_nl($string) {
    return nl2br(e($string));
}

/**
 * URL编码
 * 
 * @param string|null $string 需要编码的字符串
 * @return string 编码后的字符串
 */
function url_encode($string) {
    return urlencode($string ?? '');
}

/**
 * 格式化日期时间
 * 
 * @param string $datetime 日期时间字符串
 * @param string $format 格式化模板
 * @return string 格式化后的日期时间
 */
function format_datetime($datetime, $format = 'Y-m-d H:i') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * 截取字符串
 * 
 * @param string $string 原字符串
 * @param int $length 截取长度
 * @param string $suffix 后缀
 * @return string 截取后的字符串
 */
function str_limit($string, $length = 100, $suffix = '...') {
    if (mb_strlen($string, 'UTF-8') <= $length) {
        return $string;
    }
    return mb_substr($string, 0, $length, 'UTF-8') . $suffix;
}

/**
 * 生成随机字符串
 * 
 * @param int $length 长度
 * @return string 随机字符串
 */
function random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * 检查是否为AJAX请求
 * 
 * @return bool
 */
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * 获取客户端IP地址
 * 
 * @return string IP地址
 */
function get_client_ip() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // 取第一个IP（如果有多个）
    if (strpos($ip, ',') !== false) {
        $ip = explode(',', $ip)[0];
    }
    
    return trim($ip);
}

/**
 * JSON响应
 * 
 * @param bool $success 是否成功
 * @param string $message 消息
 * @param array $data 数据
 */
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 重定向
 * 
 * @param string $url 目标URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

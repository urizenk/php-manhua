<?php
/**
 * 安全HTTP头设置
 * 在所有后台页面的header中引入此文件
 */

// 防止点击劫持
header("X-Frame-Options: DENY");

// 防止MIME类型嗅探
header("X-Content-Type-Options: nosniff");

// XSS保护
header("X-XSS-Protection: 1; mode=block");

// Referrer策略
header("Referrer-Policy: strict-origin-when-cross-origin");

// 内容安全策略（CSP）
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self';");

// 严格传输安全（HSTS）- 仅在HTTPS环境启用
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// 权限策略
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

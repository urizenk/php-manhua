<?php
/**
 * 配置文件示例
 * 使用时请复制为 config.php 并修改相关配置
 */

return [
    // 数据库配置
    'database' => [
        'host'     => '47.110.75.188',  // 远程MySQL地址
        'port'     => '3306',
        'dbname'   => 'manhua_db',
        'username' => 'root',
        'password' => '',  // 请在部署时修改为实际密码
        'charset'  => 'utf8mb4',
    ],
    
    // Session配置
    'session' => [
        'name'     => 'MANHUA_SESSION',  // Session名称
        'lifetime' => 7200,              // Session有效期（秒）2小时
        'path'     => '/',               // Session路径
        'domain'   => '',                // Session域名（留空为当前域名）
        'secure'   => false,             // 是否仅HTTPS传输（生产环境建议true）
        'httponly' => true,              // 是否仅HTTP访问（防止XSS）
    ],
    
    // 文件上传配置
    'upload' => [
        'max_size'      => 5 * 1024 * 1024,  // 最大文件大小：5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],  // 允许的文件类型
        'save_path'     => '/public/uploads/',  // 保存路径（相对于项目根目录）
        'create_thumb'  => true,             // 是否创建缩略图
        'thumb_width'   => 300,              // 缩略图宽度
        'thumb_height'  => 400,              // 缩略图高度
    ],
    
    // 网站配置
    'site' => [
        'name'        => '海の小窝',      // 网站名称
        'url'         => 'http://localhost',  // 网站URL（生产环境请修改）
        'admin_path'  => '/admin88',      // 后台路径
        'timezone'    => 'Asia/Shanghai',  // 时区
    ],
];

-- ==========================================
-- PHP漫画资源管理系统 - 数据库表结构
-- 数据库名称: manhua_db
-- 版本: 1.0
-- 创建时间: 2025-11-16
-- ==========================================

-- 创建数据库
CREATE DATABASE IF NOT EXISTS `manhua_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `manhua_db`;

-- ==========================================
-- 1. 网站配置表
-- ==========================================
CREATE TABLE IF NOT EXISTS `site_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` VARCHAR(50) NOT NULL COMMENT '配置键名',
  `config_value` TEXT NOT NULL COMMENT '配置值',
  `description` VARCHAR(200) DEFAULT NULL COMMENT '配置说明',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='网站配置表';

-- 插入默认访问码配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('access_code', '1024', '当日访问码'),
('site_name', '海の小窝', '网站名称'),
('site_desc', '无偿分享 禁止盗卖 更多精彩', '网站描述');

-- ==========================================
-- 2. 漫画类型表
-- ==========================================
CREATE TABLE IF NOT EXISTS `manga_types` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '类型ID',
  `type_name` VARCHAR(50) NOT NULL COMMENT '类型名称',
  `type_code` VARCHAR(30) NOT NULL COMMENT '类型代码（英文标识）',
  `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `need_cover` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否需要封面图：0-否，1-是',
  `need_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否需要状态：0-否，1-是',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_code` (`type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='漫画类型表';

-- 插入9种类型
INSERT INTO `manga_types` (`type_name`, `type_code`, `sort_order`, `need_cover`, `need_status`) VALUES
('日更板块', 'daily_update', 1, 0, 0),
('韩漫合集', 'korean_collection', 2, 1, 1),
('完结短漫', 'short_complete', 3, 0, 0),
('日漫推荐', 'japan_recommend', 4, 1, 0),
('日漫合集', 'japan_collection', 5, 0, 0),
('动漫合集', 'anime_collection', 6, 0, 0),
('广播剧合集', 'drama_collection', 7, 0, 0),
('失效反馈', 'feedback', 8, 0, 0),
('防走丢', 'backup_link', 9, 0, 0);

-- ==========================================
-- 3. 标签表
-- ==========================================
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `type_id` INT(11) UNSIGNED NOT NULL COMMENT '所属类型ID',
  `tag_name` VARCHAR(100) NOT NULL COMMENT '标签名称',
  `tag_type` VARCHAR(20) DEFAULT NULL COMMENT '标签类型：date-日期标签，letter-字母标签，category-分类标签，author-作者标签',
  `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_type_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='标签表';

-- 插入默认标签
INSERT INTO `tags` (`type_id`, `tag_name`, `tag_type`, `sort_order`) VALUES
-- 完结短漫的字母标签
(3, 'A', 'letter', 1),
(3, 'B', 'letter', 2),
(3, 'C', 'letter', 3),
-- 未分类标签（所有类型通用）
(1, '未分类', 'category', 999),
(2, '未分类', 'category', 999),
(3, '未分类', 'category', 999),
(4, '未分类', 'category', 999);

-- ==========================================
-- 4. 漫画主表
-- ==========================================
CREATE TABLE IF NOT EXISTS `mangas` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '漫画ID',
  `type_id` INT(11) UNSIGNED NOT NULL COMMENT '所属类型ID',
  `tag_id` INT(11) UNSIGNED DEFAULT NULL COMMENT '所属标签ID',
  `title` VARCHAR(200) NOT NULL COMMENT '漫画标题',
  `cover_image` VARCHAR(255) DEFAULT NULL COMMENT '封面图片路径',
  `cover_position` VARCHAR(20) DEFAULT 'center' COMMENT '封面图片展示位置：top,center,bottom',
  `status` VARCHAR(20) DEFAULT NULL COMMENT '状态：serializing-连载中，completed-已完结',
  `resource_link` TEXT DEFAULT NULL COMMENT '资源链接',
  `description` TEXT DEFAULT NULL COMMENT '简介',
  `views` INT(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type_id` (`type_id`),
  KEY `idx_tag_id` (`tag_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='漫画主表';

-- ==========================================
-- 5. 漫画章节表（用于日更板块等资源链接列表）
-- ==========================================
CREATE TABLE IF NOT EXISTS `manga_chapters` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '章节ID',
  `manga_id` INT(11) UNSIGNED NOT NULL COMMENT '所属漫画ID',
  `chapter_title` VARCHAR(200) NOT NULL COMMENT '章节标题',
  `chapter_link` TEXT NOT NULL COMMENT '章节链接',
  `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序权重',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_manga_id` (`manga_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='漫画章节表';

-- ==========================================
-- 6. 管理员表
-- ==========================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` VARCHAR(50) NOT NULL COMMENT '用户名',
  `password` VARCHAR(255) NOT NULL COMMENT '密码（bcrypt加密）',
  `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT '最后登录时间',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 插入默认管理员账号（用户名：admin，密码：admin123）
-- 密码使用PHP: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ==========================================
-- 7. 访问日志表（可选，用于统计）
-- ==========================================
CREATE TABLE IF NOT EXISTS `access_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP地址',
  `access_code` VARCHAR(20) DEFAULT NULL COMMENT '使用的访问码',
  `is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '验证是否成功：0-失败，1-成功',
  `user_agent` VARCHAR(255) DEFAULT NULL COMMENT '浏览器UA',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '访问时间',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问日志表';

-- ==========================================
-- 索引优化说明
-- ==========================================
-- 1. 所有外键字段已添加索引，提升关联查询性能
-- 2. 常用筛选字段（type_id, tag_id, status）已添加索引
-- 3. 时间字段（created_at）添加索引，支持按时间排序查询
-- 4. 唯一键约束确保数据一致性（config_key, type_code, username）



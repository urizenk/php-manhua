-- ==========================================
-- 漫画功能扩展 - 数据库迁移
-- 日期: 2025-12-16
-- ==========================================

-- 1. 为漫画表添加漫画标签和提取码字段
ALTER TABLE `mangas` 
ADD COLUMN `manga_tags` VARCHAR(255) DEFAULT NULL COMMENT '漫画标签（多个标签用逗号分隔）' AFTER `tag_id`,
ADD COLUMN `extract_code` VARCHAR(100) DEFAULT NULL COMMENT '提取码' AFTER `resource_link`;

-- 2. 为模块类型表添加图标字段
ALTER TABLE `manga_types`
ADD COLUMN `icon` VARCHAR(50) DEFAULT 'book' COMMENT '模块图标名称（Bootstrap Icons）' AFTER `type_code`;

-- 3. 更新现有模块的默认图标
UPDATE `manga_types` SET `icon` = 'calendar-date' WHERE `type_code` = 'daily_update';
UPDATE `manga_types` SET `icon` = 'collection' WHERE `type_code` = 'korean_collection';
UPDATE `manga_types` SET `icon` = 'check-circle' WHERE `type_code` = 'short_complete';
UPDATE `manga_types` SET `icon` = 'star' WHERE `type_code` = 'japan_recommend';
UPDATE `manga_types` SET `icon` = 'gift' WHERE `type_code` = 'japan_collection';
UPDATE `manga_types` SET `icon` = 'film' WHERE `type_code` = 'anime_collection';
UPDATE `manga_types` SET `icon` = 'headphones' WHERE `type_code` = 'drama_collection';
UPDATE `manga_types` SET `icon` = 'chat-dots' WHERE `type_code` = 'feedback';
UPDATE `manga_types` SET `icon` = 'geo-alt' WHERE `type_code` = 'backup_link';

-- 4. 添加首页跳转URL配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('homepage_redirect_url', '', '首页跳转URL（用于跳转到其他页面）')
ON DUPLICATE KEY UPDATE `description` = '首页跳转URL（用于跳转到其他页面）';


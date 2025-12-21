-- 添加访问码获取地址列表配置（JSON格式，支持多个）
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('access_code_urls', '[]', '访问码获取地址列表')
ON DUPLICATE KEY UPDATE `description` = '访问码获取地址列表';

-- 更新访问码获取教程说明
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('access_code_tutorial', '', '访问码获取教程')
ON DUPLICATE KEY UPDATE `description` = '访问码获取教程';

-- 为模块添加外部链接字段（如果不存在）
-- ALTER TABLE `manga_types` ADD COLUMN `external_url` VARCHAR(500) DEFAULT NULL COMMENT '外部跳转链接';

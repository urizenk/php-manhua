-- 添加访问码获取地址配置
-- 执行时间: 2024-12-21

-- 1. 添加访问码获取URL配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('access_code_url', '', '访问码获取地址（用户点击后跳转获取访问码）'),
('access_code_tutorial', '关注主页即可获取每日访问码', '访问码获取提示文字')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 2. 为模块添加外部链接字段（用于失效反馈、防走丢等需要跳转到外部URL的模块）
ALTER TABLE `manga_types` 
ADD COLUMN `external_url` VARCHAR(500) DEFAULT NULL COMMENT '外部跳转链接（用于反馈、防走丢等模块）' AFTER `need_status`;


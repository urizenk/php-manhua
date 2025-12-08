-- ==========================================
-- 添加微博配置到 site_config 表
-- ==========================================

USE `manhua_db`;

-- 插入微博配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('weibo_url', 'https://weibo.com/', '微博主页链接'),
('weibo_text', '微博@资源小站', '微博按钮显示文本')
ON DUPLICATE KEY UPDATE 
    `config_value` = VALUES(`config_value`),
    `description` = VALUES(`description`);

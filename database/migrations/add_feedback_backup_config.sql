-- 失效反馈页面配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('feedback_qq', '123456789', 'QQ群号'),
('feedback_email', 'feedback@example.com', '反馈邮箱'),
('feedback_notice', '如果您发现资源链接失效、无法访问或其他问题，请通过以下方式联系我们。反馈时请说明具体的资源名称和问题描述，我们会尽快处理。', '反馈须知文本')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 防走丢页面配置
INSERT INTO `site_config` (`config_key`, `config_value`, `description`) VALUES
('backup_urls', '[]', '备用地址列表(JSON)'),
('backup_notice', '为防止主站无法访问，请收藏以下备用地址。建议将地址保存到浏览器书签或记事本中。', '防走丢提示文本')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);


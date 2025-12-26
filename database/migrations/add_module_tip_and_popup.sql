-- 添加模块提示文字和弹窗图片字段
-- 执行时间: 2025-12-26

-- 添加tip_text字段 - 用于自定义模块页面的提示文字
ALTER TABLE `manga_types` ADD COLUMN `tip_text` VARCHAR(500) DEFAULT NULL COMMENT '模块页面提示文字' AFTER `external_url`;

-- 添加popup_image字段 - 用于点击模块后显示的图片
ALTER TABLE `manga_types` ADD COLUMN `popup_image` VARCHAR(255) DEFAULT NULL COMMENT '弹窗显示图片路径' AFTER `tip_text`;

-- 添加icon字段（如果不存在）
-- ALTER TABLE `manga_types` ADD COLUMN `icon` VARCHAR(50) DEFAULT 'book' COMMENT '模块图标' AFTER `sort_order`;


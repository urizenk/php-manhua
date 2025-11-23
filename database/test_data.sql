-- ==========================================
-- 测试数据SQL文件
-- 用于完整功能测试
-- 使用说明：先导入schema.sql，再导入此文件
-- ==========================================

USE manhua_db;

-- ==========================================
-- 1. 清空现有测试数据（保留默认数据）
-- ==========================================
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM manga_chapters WHERE manga_id > 0;
DELETE FROM mangas WHERE id > 0;
DELETE FROM tags WHERE id > 9;  -- 保留默认的9个"未分类"标签
DELETE FROM access_logs WHERE id > 0;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- 2. 插入标签测试数据
-- ==========================================

-- 日更板块的日期标签
INSERT INTO `tags` (`type_id`, `tag_name`, `tag_type`, `sort_order`) VALUES
(1, '2025-01-15', 'date', 100),
(1, '2025-01-14', 'date', 99),
(1, '2025-01-13', 'date', 98),
(1, '2025-01-12', 'date', 97),
(1, '2025-01-11', 'date', 96);

-- 韩漫合集的分类标签
INSERT INTO `tags` (`type_id`, `tag_name`, `tag_type`, `sort_order`) VALUES
(2, '都市言情', 'category', 10),
(2, '玄幻修仙', 'category', 9),
(2, '悬疑推理', 'category', 8),
(2, '校园青春', 'category', 7),
(2, '古风历史', 'category', 6);

-- 完结短漫的字母标签
INSERT INTO `tags` (`type_id`, `tag_name`, `tag_type`, `sort_order`) VALUES
(3, 'A', 'letter', 26),
(3, 'B', 'letter', 25),
(3, 'C', 'letter', 24),
(3, 'D', 'letter', 23),
(3, 'M', 'letter', 22),
(3, 'S', 'letter', 21);

-- 日漫推荐的作者标签
INSERT INTO `tags` (`type_id`, `tag_name`, `tag_type`, `sort_order`) VALUES
(4, '尾田荣一郎', 'author', 20),
(4, '岸本齐史', 'author', 19),
(4, '鸟山明', 'author', 18),
(4, '青山刚昌', 'author', 17),
(4, '井上雄彦', 'author', 16),
(4, '高桥留美子', 'author', 15);

-- ==========================================
-- 3. 插入漫画测试数据
-- ==========================================

-- 日更板块漫画（10条）
INSERT INTO `mangas` (`type_id`, `tag_id`, `title`, `cover_image`, `status`, `resource_link`, `description`, `views`, `sort_order`) VALUES
(1, 10, '今日热门：霸道总裁的小娇妻', NULL, NULL, 'https://pan.quark.cn/s/abc123', '都市言情，甜宠文，日更连载中...', 1580, 100),
(1, 10, '修仙归来在都市', NULL, NULL, 'https://pan.baidu.com/s/def456', '玄幻都市，爽文，每日更新', 2341, 99),
(1, 11, '昨日推荐：神医毒妃不好惹', NULL, NULL, 'https://pan.xunlei.com/s/ghi789', '古风穿越，女强文', 987, 98),
(1, 11, '星际战舰指挥官', NULL, NULL, 'https://pan.quark.cn/s/jkl012', '科幻题材，热血冒险', 1245, 97),
(1, 12, '三天前：全能学霸的异世界', NULL, NULL, 'https://pan.baidu.com/s/mno345', '异世界冒险，轻松搞笑', 876, 96),
(1, 12, '豪门重生：千金归来', NULL, NULL, 'https://pan.quark.cn/s/pqr678', '豪门恩怨，复仇爽文', 1523, 95),
(1, 13, '四天前：末世求生指南', NULL, NULL, 'https://pan.xunlei.com/s/stu901', '末世生存，紧张刺激', 2100, 94),
(1, 13, '娱乐圈之巨星养成', NULL, NULL, 'https://pan.baidu.com/s/vwx234', '娱乐圈，励志成长', 1678, 93),
(1, 14, '五天前：仙侠奇缘录', NULL, NULL, 'https://pan.quark.cn/s/yza567', '仙侠修真，唯美浪漫', 945, 92),
(1, 14, '赛博朋克2088', NULL, NULL, 'https://pan.xunlei.com/s/bcd890', '赛博朋克，科幻悬疑', 1834, 91);

-- 韩漫合集（15条，包含连载和完结）
INSERT INTO `mangas` (`type_id`, `tag_id`, `title`, `cover_image`, `status`, `resource_link`, `description`, `views`, `sort_order`, `cover_position`) VALUES
(2, 15, '恋爱禁区', '/public/uploads/test/korean1.jpg', 'serializing', 'https://pan.quark.cn/s/korean001', '现代都市爱情故事，甜蜜互动，每周更新', 5678, 100, 'center'),
(2, 15, '总裁的契约新娘', '/public/uploads/test/korean2.jpg', 'completed', 'https://pan.baidu.com/s/korean002', '霸道总裁遇上倔强少女，已完结', 8934, 99, 'top'),
(2, 16, '仙界传说', '/public/uploads/test/korean3.jpg', 'serializing', 'https://pan.xunlei.com/s/korean003', '修仙世界，热血冒险，连载中', 4521, 98, 'center'),
(2, 16, '魔法学院的日常', '/public/uploads/test/korean4.jpg', 'completed', 'https://pan.quark.cn/s/korean004', '魔法校园，青春成长，全129话', 7234, 97, 'top'),
(2, 17, '密室逃脱游戏', '/public/uploads/test/korean5.jpg', 'serializing', 'https://pan.baidu.com/s/korean005', '悬疑推理，烧脑剧情', 6789, 96, 'center'),
(2, 17, '午夜怪谈', '/public/uploads/test/korean6.jpg', 'completed', 'https://pan.xunlei.com/s/korean006', '恐怖悬疑，惊悚故事，已完结', 5432, 95, 'center'),
(2, 18, '樱花树下的约定', '/public/uploads/test/korean7.jpg', 'serializing', 'https://pan.quark.cn/s/korean007', '校园青春，纯爱故事', 9876, 94, 'top'),
(2, 18, '篮球少年', '/public/uploads/test/korean8.jpg', 'completed', 'https://pan.baidu.com/s/korean008', '热血体育，励志成长，全156话', 6543, 93, 'center'),
(2, 19, '穿越大唐', '/public/uploads/test/korean9.jpg', 'serializing', 'https://pan.xunlei.com/s/korean009', '古风穿越，宫廷权谋', 7890, 92, 'center'),
(2, 19, '锦绣未央', '/public/uploads/test/korean10.jpg', 'completed', 'https://pan.quark.cn/s/korean010', '古代宅斗，智斗群雄，已完结', 8765, 91, 'top'),
(2, 15, '咖啡厅的秘密', '/public/uploads/test/korean11.jpg', 'serializing', 'https://pan.baidu.com/s/korean011', '都市温情，治愈系', 4321, 90, 'center'),
(2, 16, '魔王转生记', '/public/uploads/test/korean12.jpg', 'completed', 'https://pan.xunlei.com/s/korean012', '异世界冒险，搞笑轻松', 5678, 89, 'center'),
(2, 17, '连环杀人案', '/public/uploads/test/korean13.jpg', 'serializing', 'https://pan.quark.cn/s/korean013', '刑侦推理，紧张刺激', 7654, 88, 'top'),
(2, 18, '夏日海滩', '/public/uploads/test/korean14.jpg', 'completed', 'https://pan.baidu.com/s/korean014', '青春恋爱，阳光沙滩', 6234, 87, 'center'),
(2, 19, '大明风华', '/public/uploads/test/korean15.jpg', 'serializing', 'https://pan.xunlei.com/s/korean015', '明朝历史，风云际会', 5432, 86, 'center');

-- 完结短漫（12条）
INSERT INTO `mangas` (`type_id`, `tag_id`, `title`, `cover_image`, `status`, `resource_link`, `description`, `views`, `sort_order`) VALUES
(3, 20, 'AI觉醒之日', NULL, 'completed', 'https://pan.quark.cn/s/short001', '科幻短篇，人工智能主题，全8话', 3456, 100),
(3, 21, 'Butterfly Effect', NULL, 'completed', 'https://pan.baidu.com/s/short002', '悬疑短篇，蝴蝶效应，全10话', 4567, 99),
(3, 21, 'Black Mirror', NULL, 'completed', 'https://pan.xunlei.com/s/short003', '科幻惊悚，全6话', 5678, 98),
(3, 22, 'Cat Cafe', NULL, 'completed', 'https://pan.quark.cn/s/short004', '治愈系，猫咪咖啡馆，全12话', 6789, 97),
(3, 22, 'Cityscape', NULL, 'completed', 'https://pan.baidu.com/s/short005', '都市风光，生活片段，全15话', 3210, 96),
(3, 23, 'Dream Walker', NULL, 'completed', 'https://pan.xunlei.com/s/short006', '奇幻冒险，入梦术，全20话', 4321, 95),
(3, 23, 'Detective Conan Style', NULL, 'completed', 'https://pan.quark.cn/s/short007', '推理侦探，致敬柯南，全16话', 7890, 94),
(3, 24, 'Memory Lane', NULL, 'completed', 'https://pan.baidu.com/s/short008', '回忆往事，温馨感人，全9话', 2345, 93),
(3, 24, 'Monster Hunter', NULL, 'completed', 'https://pan.xunlei.com/s/short009', '猎魔题材，热血战斗，全18话', 8901, 92),
(3, 25, 'Silent Night', NULL, 'completed', 'https://pan.quark.cn/s/short010', '悬疑恐怖，寂静之夜，全7话', 3456, 91),
(3, 25, 'Star Traveler', NULL, 'completed', 'https://pan.baidu.com/s/short011', '星际旅行，科幻冒险，全14话', 5678, 90),
(3, 25, 'Summer Romance', NULL, 'completed', 'https://pan.xunlei.com/s/short012', '夏日恋情，青春爱情，全11话', 4567, 89);

-- 日漫推荐（20条，用于测试分页18本/页）
INSERT INTO `mangas` (`type_id`, `tag_id`, `title`, `cover_image`, `status`, `resource_link`, `description`, `views`, `sort_order`, `cover_position`) VALUES
(4, 26, '海贼王', '/public/uploads/test/onepiece.jpg', 'serializing', 'https://pan.quark.cn/s/onepiece', '尾田荣一郎经典之作，冒险寻宝', 99999, 100, 'center'),
(4, 27, '火影忍者', '/public/uploads/test/naruto.jpg', 'completed', 'https://pan.baidu.com/s/naruto', '忍者世界，热血成长', 88888, 99, 'top'),
(4, 28, '龙珠', '/public/uploads/test/dragonball.jpg', 'completed', 'https://pan.xunlei.com/s/dragonball', '鸟山明神作，经典战斗', 77777, 98, 'center'),
(4, 29, '名侦探柯南', '/public/uploads/test/conan.jpg', 'serializing', 'https://pan.quark.cn/s/conan', '推理侦探，案件解谜', 66666, 97, 'top'),
(4, 30, '灌篮高手', '/public/uploads/test/slamdunk.jpg', 'completed', 'https://pan.baidu.com/s/slamdunk', '篮球题材，青春热血', 55555, 96, 'center'),
(4, 31, '犬夜叉', '/public/uploads/test/inuyasha.jpg', 'completed', 'https://pan.xunlei.com/s/inuyasha', '战国时代，妖怪传奇', 44444, 95, 'center'),
(4, 26, '进击的巨人', '/public/uploads/test/aot.jpg', 'completed', 'https://pan.quark.cn/s/aot', '末世求生，巨人之谜', 98765, 94, 'top'),
(4, 27, '鬼灭之刃', '/public/uploads/test/kimetsu.jpg', 'completed', 'https://pan.baidu.com/s/kimetsu', '大正时代，鬼杀队传说', 87654, 93, 'center'),
(4, 28, '我的英雄学院', '/public/uploads/test/mha.jpg', 'serializing', 'https://pan.xunlei.com/s/mha', '超能力学院，英雄养成', 76543, 92, 'center'),
(4, 29, '东京食尸鬼', '/public/uploads/test/tokyoghoul.jpg', 'completed', 'https://pan.quark.cn/s/tokyoghoul', '黑暗奇幻，人鬼交织', 65432, 91, 'top'),
(4, 30, '排球少年', '/public/uploads/test/haikyuu.jpg', 'completed', 'https://pan.baidu.com/s/haikyuu', '排球竞技，团队协作', 54321, 90, 'center'),
(4, 31, '死亡笔记', '/public/uploads/test/deathnote.jpg', 'completed', 'https://pan.xunlei.com/s/deathnote', '智斗对决，正义与邪恶', 43210, 89, 'top'),
(4, 26, '银魂', '/public/uploads/test/gintama.jpg', 'completed', 'https://pan.quark.cn/s/gintama', '搞笑恶搞，江户时代', 32109, 88, 'center'),
(4, 27, '全职猎人', '/public/uploads/test/hunterxhunter.jpg', 'serializing', 'https://pan.baidu.com/s/hxh', '冒险寻宝，念能力', 21098, 87, 'center'),
(4, 28, '钢之炼金术师', '/public/uploads/test/fma.jpg', 'completed', 'https://pan.xunlei.com/s/fma', '炼金术，兄弟情深', 19876, 86, 'top'),
(4, 29, '妖精的尾巴', '/public/uploads/test/fairytail.jpg', 'completed', 'https://pan.quark.cn/s/fairytail', '魔法公会，热血友情', 18765, 85, 'center'),
(4, 30, '黑子的篮球', '/public/uploads/test/kuroko.jpg', 'completed', 'https://pan.baidu.com/s/kuroko', '篮球竞技，奇迹世代', 17654, 84, 'center'),
(4, 31, '浪客剑心', '/public/uploads/test/rurouni.jpg', 'completed', 'https://pan.xunlei.com/s/rurouni', '明治维新，剑客传说', 16543, 83, 'top'),
(4, 26, '七龙珠超', '/public/uploads/test/dbs.jpg', 'serializing', 'https://pan.quark.cn/s/dbs', '龙珠续作，宇宙战斗', 15432, 82, 'center'),
(4, 27, '约定的梦幻岛', '/public/uploads/test/tpn.jpg', 'completed', 'https://pan.baidu.com/s/tpn', '悬疑逃脱，孤儿院之谜', 14321, 81, 'center');

-- ==========================================
-- 4. 插入章节测试数据（用于日更板块详情页）
-- ==========================================

-- 为第一个日更漫画添加章节
INSERT INTO `manga_chapters` (`manga_id`, `chapter_title`, `chapter_link`, `sort_order`) VALUES
(1, '第1话：初遇', 'https://pan.quark.cn/s/chapter001', 1),
(1, '第2话：误会', 'https://pan.quark.cn/s/chapter002', 2),
(1, '第3话：告白', 'https://pan.quark.cn/s/chapter003', 3),
(1, '第4话：纠葛', 'https://pan.quark.cn/s/chapter004', 4),
(1, '第5话：和解', 'https://pan.quark.cn/s/chapter005', 5),
(1, '第6话：甜蜜', 'https://pan.quark.cn/s/chapter006', 6);

-- 为第二个日更漫画添加章节
INSERT INTO `manga_chapters` (`manga_id`, `chapter_title`, `chapter_link`, `sort_order`) VALUES
(2, '第1章：归来', 'https://pan.baidu.com/s/chapter101', 1),
(2, '第2章：重逢', 'https://pan.baidu.com/s/chapter102', 2),
(2, '第3章：复仇', 'https://pan.baidu.com/s/chapter103', 3),
(2, '第4章：突破', 'https://pan.baidu.com/s/chapter104', 4),
(2, '第5章：飞升', 'https://pan.baidu.com/s/chapter105', 5);

-- ==========================================
-- 5. 插入访问日志测试数据
-- ==========================================

INSERT INTO `access_logs` (`ip_address`, `access_code`, `is_success`, `user_agent`) VALUES
('192.168.1.100', '1024', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'),
('192.168.1.101', '0000', 0, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15'),
('192.168.1.102', '1024', 1, 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148'),
('192.168.1.103', '1024', 1, 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 Chrome/119.0.0.0'),
('192.168.1.104', 'wrong', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0.0.0'),
('192.168.1.105', '1024', 1, 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) Safari/604.1'),
('192.168.1.106', '1024', 1, 'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0'),
('192.168.1.107', 'test', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');

-- ==========================================
-- 6. 更新统计数据
-- ==========================================

-- 更新某些漫画的浏览量
UPDATE mangas SET views = views + FLOOR(RAND() * 1000) WHERE id <= 10;

-- ==========================================
-- 测试数据插入完成
-- ==========================================

-- 统计信息
SELECT 
    '测试数据插入完成' AS status,
    (SELECT COUNT(*) FROM mangas) AS total_mangas,
    (SELECT COUNT(*) FROM tags WHERE tag_name != '未分类') AS total_tags,
    (SELECT COUNT(*) FROM manga_chapters) AS total_chapters,
    (SELECT COUNT(*) FROM access_logs) AS total_logs;

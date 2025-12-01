<?php
session_start();
require 'config.php';
require 'functions.php'; // 引入辅助函数文件

// 基础会话验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证
try {
    // 从缓存或数据库获取最新密码ID
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id', 18000, $latest_password_id); // 缓存5分钟
        }
    }

    // 对比会话中的密码版本
    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // 降级处理：允许访问但记录日志
    error_log("密码版本验证失败: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>完结短漫</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0; /* 奶黄色背景 */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-start; /* 内容靠左 */
            align-items: flex-start; /* 内容从顶部开始排列 */
            min-height: 100vh; /* 确保页面高度占满整个视口 */
            padding-top: 20px; /* 顶部留出间距 */
            padding-left: 20px; /* 左侧留出间距 */
        }

        /* 容器样式 */
        .container {
            text-align: left; /* 内容靠左 */
            max-width: 600px; /* 限制容器宽度 */
            width: 100%;
        }

        /* 页面标题样式 */
        .title {
            color: #1B1212d0; /* 深灰色字体 */
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0; /* 上下内边距 */
            display: block; /* 独占一行 */
            margin-bottom: 20px; /* 标题与内容之间的间距 */
        }

        /* A 标签的标题样式（橙色背景，去掉【】） */
        .a-title {
            display: inline-block;
            background-color: #FFA500; /* 橙色背景 */
            color: white; /* 白色字体 */
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin: 10px 0; /* 上下外边距相同 */
            border-radius: 5px; /* 圆角矩形 */
        }

        /* 超链接样式 */
        a {
            display: block; /* 让链接独占一行 */
            background-color: transparent; /* 透明背景 */
            color: #0077ff; /* 蓝色字体 */
            text-decoration: none; /* 去除下划线 */
            padding: 8px 15px; /* 设置内边距 */
            margin: 3px 0; /* 链接之间的行间距 */
            font-size: 16px;
            line-height: 1.0; /* 增加行间距 */
            width: fit-content; /* 让链接宽度自适应内容 */
            border: 1px solid transparent; /* 添加透明边框 */
            box-sizing: border-box; /* 确保内边距和边框包含在元素宽度内 */
            transition: color 0.3s ease; /* 添加过渡效果 */
        }
        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 浅橙色背景 */
            padding: 8px 12px; /* 内边距 */
            border-radius: 25px; /* 圆角 */
            margin-bottom: 20px; /* 与下方内容的间距 */
            font-size: 14px;
            color: #333;
            display: block; /* 修改为块级元素，独占一行 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        /* 回到目录按钮样式 */
        .back-to-index {
            display: block; /* 修改为块级元素，独占一行 */
            background-color: #EC5800; /* 绿色背景 */
            color: white; /* 白色文字 */
            padding: 10px 15px;
            border-radius: 5px; /* 圆角矩形 */
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* 与下方内容的间距 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        .back-to-index:hover {
            background-color: #e69500; /* 悬停时颜色加深 */
        }

        /* 鼠标悬停时的效果 */
        a:hover {
            color: #D18E85; /* 悬停时字体变为莫兰迪粉 */
            border-radius: 4px; /* 添加圆角 */
        }

        /* 点击效果 */
        a:active {
            background-color: #e0e0e0; /* 点击时背景色加深 */
        }

        /* 小标题样式（如“连载中”、“完结”） */
        h4 {
            margin: 15px 0 10px 0; /* 上下外边距 */
            font-size: 18px;
            color: #333;
        }

        /* 新增搜索框和查看按钮样式 */
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px; /* 搜索框和按钮之间的间距 */
            margin-bottom: 20px;
        }

        #searchInput {
            flex: 1; /* 搜索框占据剩余空间 */
            padding: 10px 15px;
            border: 2px solid #FFA500;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        #searchInput:focus {
            box-shadow: 0 0 8px rgba(255,165,0,0.3);
        }

        #viewButton {
            padding: 10px 20px;
            background-color: #FFA500;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #viewButton:hover {
            background-color: #e69500; /* 悬停时颜色加深 */
        }

        .search-result {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
            padding-left: 10px;
        }

        /* 高亮匹配文字 */
        .highlight {
            background-color: #FFA50033;
            color: #FFA500;
            font-weight: bold;
            padding: 0 2px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="title">完结短漫</span></h1>
        <div class="tip">
            Tip ：密码就是每日访问码，一码通用哦！
        </div>
        <a href="index.php" class="back-to-index"><i class="fas fa-arrow-left"></i>回到目录</a>

        <!-- 搜索框 -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="漫名不用打全称，用关键词搜索...">
            <button id="viewButton">查看</button>
            <div class="search-result" id="searchResult"></div>
        </div>

        <!-- 动漫列表 -->
        
       <!-- <div>
            <span class="a-title">保存所有</span>
            <a href="https://pan.quark.cn/s/82e65bf12735">点我点我</a> 

        </div> -->
        
        <div>
            <span class="a-title">A</span>
            <a href="https://pan.baidu.com/s/1D7xjWb12fXr_-aZg7AgxFw?pwd=bleh">ACT OUT1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/a76759bf-5adc-454b-854b-a2a4fd0659bf?code=KEKW5D&embed=true">爱恨缠绵1-10完结</a>
            <a href="https://flowus.cn/seacomic2/share/3adfb090-aa31-4f44-b6fa-dc98056ffe0d?code=KEKW5D&embed=true">爱情滴答滴 1-6完结</a>
            <a href="https://flowus.cn/seacomic2/share/9bbc4eab-c611-46fc-9273-98dca91f521e?code=KEKW5D&embed=true">肮脏的欲望1-10+外传完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2of2qh">艾斯德的庭院1-6完结</a>
        </div>
        
        <div>
            <span class="a-title">B</span>
            <a href="https://flowus.cn/seacomic2/share/08b5fe37-f9f7-4124-863a-7124ca4a91c1?code=KEKW5D&embed=true">爸爸和我1-5完结</a>
            <a href="https://pan.baidu.com/s/1XcluOJyXbs_yGbURd-IWGA?pwd=bleh ">Blazing_1-10完结</a>
            <a href="https://flowus.cn/seacomic2/share/75e166db-ebd9-4ae6-8564-3f8570cb6331?code=KEKW5D&embed=true">贝果太过分了/童颜太过分了！1-5完结</a>
            <a href="https://flowus.cn/seacomic2/share/398be257-9184-429a-89d4-33c7c4dd54b0?code=KEKW5D&embed=true">B273号房1-4完结</a>
            <a href="https://pan.baidu.com/s/1R2emCd0P1pe_NRsBdgk9Vw?pwd=bleh">Big Slick1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2t6f5a">白夜1-5完结</a>
            <a href="https://pan.baidu.com/s/1Fut78scHQRBKwbiwPXRE1g?pwd=bleh">爸爸情结1-5完</a>
            <a href="https://flowus.cn/seacomic2/share/16b3aa40-418c-47c2-97e2-708f8aca8597?code=KEKW5D&embed=true">被囚禁的画家1-9第一季完结</a>
            <a href="https://pan.baidu.com/s/1AeJq-4pyaqTVAq0mGwNLaw?pwd=bleh">被抓捕的恶魔 1-6 完结</a>
            <a href="https://pan.baidu.com/s/1rGWyIzf_x0VB28LHtLtObA?pwd=bleh">不是这样的1-5完结</a>
            <a href="https://pan.baidu.com/s/1T_pQcVoBPIoYulj5zcdF1g?pwd=bleh">拜托了降落伞!1-5完结</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2jeq0j">beta小伯爵的重生</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2l7l8h">不敢高攀的大公1-8完结</a>
            <a href="https://pan.baidu.com/s/1GYk3fK5u6drKrXyZvahUsw?pwd=bleh">捕食者Predator台无光1-6完结</a>
            <a href="https://flowus.cn/seacomic2/share/aeedba0f-7c43-4f46-8440-b2e37e493625?code=KEKW5D&embed=true">不要向石桥扔石头1-10完结</a>
            <a href="https://flowus.cn/seacomic2/share/66ea2940-20c9-403d-bb08-90ab974b1ff3?code=KEKW5D&embed=true">八男七夜/七日八色 台无光正篇14外传2完结</a>
        </div>
        
        <div>
            <span class="a-title">C</span>
            <a href="https://flowus.cn/seacomic2/share/5a93f1be-a03d-45f5-921f-b39beb08e904?code=KEKW5D&embed=true">诚实的债务人1-5完结</a>
            <a href="https://pan.baidu.com/s/1L3KKDm9GnGXMnV4RPEPFMA?pwd=bleh">成为阿尔法的原因 1-6完结</a>
            <a href="https://pan.baidu.com/s/1o184I6514_r7wb8XSf2NSQ?pwd=bleh">COLOSSAL 单行本 1-2</a>
            <a href="https://pan.baidu.com/s/1gD21H9AmpKwJG17GiAL2Fw?pwd=bleh">【SS系列】诚信守则1-6</a>
            <a href="https://pan.baidu.com/s/1PL6kRhZfCMMv7xj_99vGjQ?pwd=bleh">【SS系列】藏匿/隐匿 台无光1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g33ohwh">赤红诅咒的骑士1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx0d">触地得分1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2n2hpc">催眠师的新娘是魔王1-9完结</a>
        </div>
        
        <div>
            <span class="a-title">D</span>
            <a href="https://flowus.cn/seacomic2/share/3aaefea9-6a23-44ff-8e66-dcdbf19ac529?code=KEKW5D&embed=true">灯下黑/灯下不明1-8完结</a>
            <a href="https://flowus.cn/seacomic2/share/a4a032cc-06fe-4999-b615-3a9460a76bc2?code=KEKW5D&embed=true">导火索 1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2nvtjc">DeepDown 1-4完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29ofqj">DEAREST</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt2j">颠覆之血 完结</a>
            <a href="https://pan.baidu.com/s/1GBzthWeT4pwykHJzYuQ9Xw?pwd=bleh">Doggy Dol无光版1-5完结</a>
            <a href="https://flowus.cn/seacomic2/share/f38cb876-a56a-469f-8042-4a1022c0cf37?code=KEKW5D&embed=true">单相思的经营战略1-10完结</a>
            <a href="https://pan.baidu.com/s/14b8-PRWQU8VDSrTPs6Gxig?pwd=bleh">对兔子好的蛇 1-7 完结</a>
            <a href="https://flowus.cn/seacomic2/share/79673f05-e8e5-4b62-aebd-348a71b07284?code=KEKW5D&embed=true">堕于彼此的兄弟 无光版1-11完结</a>
        </div>
        <div>
            <span class="a-title">E</span>
            <a href="https://flowus.cn/seacomic2/share/b11e8646-8250-48e9-8d50-bd5dd2db6ef4?code=KEKW5D&embed=true">Emotion1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ls4qj">恶魔也疯狂一起来rock</a>
        </div>
        
        <div>
            <span class="a-title">F</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2of2xe">父亲的玩具</a>
            <a href="https://pan.baidu.com/s/1garULiDIputkOIHrds147Q?pwd=bleh ">Fall of Alpha1-2完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvvi">反向陷阱1-3完结</a>
            <a href="https://pan.baidu.com/s/1ZbTLx_wvegX2UtWlojvWCA?pwd=bleh">分享弟夫1-6完</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2qm7di">放下辣椒再说1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ltyih">F to F 1-8完结</a>
        </div>
        
        <div>
            <span class="a-title">G</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2ac4ij">GROAN1-10完结</a>
             
            <a href="https://pan.baidu.com/s/1ZmSZ0YFvXyEfex_xAVZoxw?pwd=kcef">告解之夜1-4完结</a>
            <a href="https://pan.baidu.com/s/1C4aIgsbq6PmAUFpf4qhGZw?pwd=bleh">狗狗老公1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/3fc8e636-f91d-415f-aa55-b4c076d97bbb?code=KEKW5D&embed=true">给傲慢弟弟的教训1-5完结</a>
            <a href="https://pan.baidu.com/s/16MNNlQtu4eUBv0KunQgrqA?pwd=bleh">哥，我是假叽叽！1-8完结</a>
            <a href="https://pan.baidu.com/s/1Qbp4D5TCKt8Q8Bj1n49F2A?pwd=kcef">隔壁邻居是吸血鬼1-4完结</a>
            <a href="https://pan.baidu.com/s/1t_KHSl0oxerCfrwTEDfcmg?pwd=bleh">狗和温暖的锅炉 1-5+外传完结</a>
        </div>
        
        <div>
            <span class="a-title">H</span>
            <a href="https://pan.baidu.com/s/1sG33jLCgKlPxf4w-q00ZOg?pwd=bleh">后辈相助1-5完结</a>
            <a href="https://pan.baidu.com/s/1UTbT4OulqlOnULZG1ikgUw?pwd=bleh">花园的秘密1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvtg">花未开（01-12完结）</a>
            <a href="https://jjnztxsb.lanpw.com/b00g2kzo3c">hand_to_hand1-4完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx1e">滑杆进洞1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/8b7b8287-3711-4b73-a001-a31ad454f315?code=KEKW5D&embed=true">和青梅竹马一起驱魔中1-8end</a>
            <a href="https://pan.baidu.com/s/1sZm07vt75OgC1KSlUFJeXw?pwd=bleh">[夜幕降临系列]Hygieia_1-4完结</a>
        </div>
        
        <div>
            <span class="a-title">I</span>
            <a href="https://jjnztxsb.lanpv.com/b00g2ls4mf">in_the_pool</a>
        </div>
        
        <div>
            <span class="a-title">J</span>
            <a href="https://pan.baidu.com/s/1ifGljAnoH87-53JDE9aWvA?pwd=bleh">姐夫1-5完结</a>
            <a href="https://flowus.cn/seacomic2/share/6410a795-0e18-4a15-94f4-ddc1cf0a1c9e?code=KEKW5D&embed=true">不准偷看我的心！无光版正篇8完结＋外传7完结</a>
            <a href="https://pan.baidu.com/s/1qEA5rDgy-r5mpTXs4oRD_A?pwd=bleh">即刻逮捕 1-9 完结</a>
            <a href="https://pan.baidu.com/s/19oPb5cQDozA0phETFkeU0w?pwd=bleh">金枝玉叶1-2完结</a>
            <a href="https://pan.baidu.com/s/1bLw7qOZfinVMXTfHt0NN-Q?pwd=bleh">姐夫 1-5完结</a>
            <a href="https://pan.xunlei.com/s/VOQn8ml_7gEIh6djYCQ4z6ZhA1?pwd=r69n#">家族的阴影之下</a>
            <a href="https://pan.baidu.com/s/1-8I3poA9NpKQ5pJoddyv8w?pwd=bleh">教授的身体论文 1-7 完结</a>
            <a href="https://pan.baidu.com/s/1NU5R_a4smu4Yl2boafb_AQ?pwd=bleh">今日份幸运物是大叔1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvuh">僵尸的那个是BX_Big（01-04完结）</a>
            <a href="https://pan.baidu.com/s/19oPb5cQDozA0phETFkeU0w?pwd=bleh">金枝玉叶1-2完结（吸血鬼攻x美人受）</a>
            
        </div>
        
        <div>
            <span class="a-title">K</span>
            <a href="https://pan.baidu.com/s/1VYYQIaiNzqoHy_eUreb8Hw?pwd=bleh">恐怖万岁 台无光1-7完结</a>
            <a href="https://pan.baidu.com/s/1x3Cxj-HDRuWobDjhpBUMgQ?pwd=bleh">可共享的青梅竹马1-6完结</a>
        </div>
        
        <div>
            <span class="a-title">L</span>
            <a href="https://pan.baidu.com/s/1xEI5dkuwI9j1rH9FYFWGig?pwd=bleh">Log in paris1-3完结</a>
            <a href="https://pan.baidu.com/s/18fCcAVWjCiNR-m8d-5jfiw?pwd=bleh">落张不入</a>
            <a href="https://flowus.cn/seacomic2/share/5804e6e8-c2aa-4a9b-bf8f-5dc790ce235b?code=KEKW5D&embed=true">RonProject/罗恩计划 无光版1-5完结</a>
            <a href="https://pan.baidu.com/s/15P4Eh3wQ8lzHP_TdWupk2w?pwd=bleh">Love and roll无光版 1-5 完结</a>
            <a href="https://pan.baidu.com/s/1d0us54_ZMeX2TLMx1DcbYQ?pwd=bleh">离别是灵丹妙药1-6完结</a>
            <a href="https://flowus.cn/seacomic2/share/01763b66-f73f-4f47-b2ce-3d86e9d2c6fd?code=KEKW5D&embed=true">垃圾也曾是新的1-15完结</a>
            <a href="https://jjnztxsb.lanpv.com/b00g2ko45i">猎人一夜要十次</a>
            <a href="https://jjnztxsb.lanpw.com/b00g2mtvsf">辣椒失踪事件1-8完结</a>
            <a href="https://jjnztxsb.lanpv.com/b00g2jeudg">利马症候群、利马综合征</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2d1sxa">老狼与小狐狸1-6完</a>
        </div>
        
        <div>
            <span class="a-title">M</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2bwy7e">魔镜啊魔镜 完结</a>
            <a href="https://pan.baidu.com/s/17Mf3Z49f8weTuwh2T9KyLQ?pwd=kcef">妈妈的粉丝来信1-5完结</a>
            <a href="https://pan.baidu.com/s/1NmXzSf6dlyKknjvrtUsvyg?pwd=bleh">Melting Home 1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/3cc07dc8-99ee-4845-abe0-a81d565bee0b?code=KEKW5D&embed=true"> My Dol Diary1-9完结</a>
            <a href="https://pan.baidu.com/s/1vD9yxJIa6juJUm3rZxshaw?pwd=bleh">没眼力见的柱子1-12+番外完结</a>
        </div>
        <div>
            <span class="a-title">N</span>
            <a href="https://pan.baidu.com/s/1s1gE7C9z6Upqt2vjktjCrA?pwd=bleh">男按摩师1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/29fd35d4-0d49-4c89-b963-8e10c5d64620?code=KEKW5D&embed=true">你让我好羞耻 台无光1-5＋外传4完结</a>
            <a href="https://flowus.cn/seacomic2/share/b7fc7b75-b3c1-46f2-beca-3d4580302198?code=KEKW5D&embed=true">你的口味怎么这样1-6完结</a>
            <a href="https://pan.baidu.com/s/1FoXeKsAfDAKbx1oaK0YtGw?pwd=bleh ">你所期望之事将不会实现1-3完结</a>
            <a href="https://pan.baidu.com/s/1u8trkijqSQpvOjX8sKA6Vw?pwd=bleh ">你能拒绝刚出炉的面包吗1-7完结</a>
            <a href="https://pan.xunlei.com/s/VOQgieHOs9gM9RovXpvUDCPLA1?pwd=yg7g#">那个男人的夜晚正篇20➕外传5完结</a>
            
        </div>
        <div>
            <span class="a-title">O</span>
            <a href="https://flowus.cn/seacomic2/share/b3c265e7-646b-4787-ace4-c501d4b02d0e?code=KEKW5D&embed=true">Omega沦陷报告1-5完结</a>
        </div>
        
        <div>
            <span class="a-title">P</span>
            <a href="https://pan.baidu.com/s/1mdzGRYDzP_QJMlTATf8MtQ?pwd=bleh">Pocket7 1-5 完结</a>
        </div>
        
        <div>
            <span class="a-title">Q</span>
            <a href="https://flowus.cn/seacomic2/share/54cc2d33-8a35-4284-9c05-29626286fc3c?code=KEKW5D&embed=true">七日八色 1-14 完结</a>
            <a href="https://flowus.cn/seacomic2/share/4c9f2ae0-a7ef-45fc-926e-e28639955b5f?code=KEKW5D&embed=true">请与我相爱1-7完结</a>
            <a href="https://pan.baidu.com/s/1RWCrz59vtP-kifM3m-AtJA?pwd=bleh">骑士兄弟1-3完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2niqyb">请喜欢我1-4完结</a>
            <a href="https://flowus.cn/seacomic2/share/509c2e7e-ef65-46e6-aa56-8305f4693ce2?code=KEKW5D&embed=true">亲密的兄弟1-8完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvxa">前列腺报告书1-6完结</a>
            <a href="https://pan.baidu.com/s/1_KUyJ5xB4Kw_TZauDvIMZQ?pwd=bleh">请吃一口年糕 1-5完结</a>
            <a href="https://pan.baidu.com/s/1hZdEej72ORCKzbsKmb3xfQ?pwd=bleh">前辈你不记得我了吗1-2完结</a>
            <a href="https://flowus.cn/seacomic2/share/ce5b3c79-4ec7-4d26-bfbf-c1e546aec15c?code=KEKW5D&embed=true">轻易陷入三千浦1-21完结</a>
        </div>
        
        <div>
            <span class="a-title">R</span>
            <a href="https://flowus.cn/seacomic2/share/5804e6e8-c2aa-4a9b-bf8f-5dc790ce235b?code=KEKW5D&embed=true">RonProject/罗恩计划 无光版1-5完结</a>
            <a href="https://pan.baidu.com/s/1X09gWTxSbaB9VmeKm3l8xQ?pwd=bleh">若被龙尾缠绕 完结</a>
        </div>
        
        <div>
            <span class="a-title">S</span>
            <a href="https://flowus.cn/seacomic2/share/fc558155-85a2-4292-802c-50c7d6be4c9d?code=KEKW5D&embed=true">首席顾问1-8完结</a>
            <a href="https://pan.baidu.com/s/1BioruTSOLzuQ3WnO9U-Gtg?pwd=bleh">S.O.S 台无光1-10完结</a>
            <a href="https://flowus.cn/seacomic2/share/8580e174-e62a-466a-a26f-4ae18f64c35e?code=KEKW5D&embed=true">疯子水花、水花splash1-7完结</a>
            <a href="https://flowus.cn/seacomic2/share/fc558155-85a2-4292-802c-50c7d6be4c9d?code=KEKW5D&embed=true">首席顾问1-8完结</a>
            <a href="https://flowus.cn/seacomic2/share/315b050d-3e44-4ec9-bc1d-3090c2c9dcb5?code=KEKW5D&embed=true">Silver Trap 1-4完结</a>
            <a href="https://pan.baidu.com/s/1kOfOTqtfk0XA4Yzf2-_9Gg?pwd=bleh">水箱、水槽 1-10 完结</a>
            <a href="https://pan.baidu.com/s/1FzMNm3XbFXXaj97dvyWbSg?pwd=bleh">stalk in love 1-10完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx2f">私密通话1-10完结</a>
            <a href="https://flowus.cn/seacomic2/share/78a209c0-b547-4459-bd65-de70219a3975?code=KEKW5D&embed=true">所以谁是0？1-7完结</a>
            <a href="https://pan.baidu.com/s/1qGYP9WOHt2wzkgkmCAESWw?pwd=bleh">丧尸的居居粗又大 1-4 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2d57gb">[短篇]使用后不能退货_全5话</a>
            <a href="https://pan.baidu.com/s/1bBGn8UCROYQAkAVVWk5w4A?pwd=bleh">圣诞节的诅咒 正篇 4 + 外传 8 完结</a>
        </div>
        
        <div>
            <span class="a-title">T</span>
            <a href="https://pan.baidu.com/s/14srgGN2_IBEaNNJK_dvDUg?pwd=bleh ">同类 1-10 完结</a>
            <a href="https://pan.baidu.com/s/1CS74pZoBIwLYaCA9KIJXtQ?pwd=bleh">糖分成瘾 台无光 1-10 完</a>
            <a href="https://pan.baidu.com/s/1GxbXNKeImDdHcIYSMWYKjw?pwd=bleh">停楠1-5完（鬼夜曲太太新作）</a>
            <a href="https://pan.xunlei.com/s/VOP_vpgxjUEt5HrukX8TyzouA1?pwd=qdss#">甜蜜的旅行1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx4h">童贞杀手1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2n5awf">three-dive1-5完结</a>
        </div>
        
        <div>
            <span class="a-title">U</span>
            <a href="https://pan.baidu.com/s/1krl4JN3zI9lmAe0AUycghg?pwd=bleh">Under the Leg1-5完结</a>
        </div>
        
        <div>
            <span class="a-title">W</span>
            
            <a href="https://pan.baidu.com/s/1JoOoSdENxRFBExpgIFw4oA?pwd=bleh ">未知区域1-7完结</a>
            <a href="https://pan.baidu.com/s/1J5CAtaqECx5G1zOiLl5-Ew?pwd=bleh">我的大哥1-3完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx6j">误会的根源1-8完结</a>
            <a href="https://flowus.cn/seacomic2/share/b2712bec-e91e-4e1e-8d3a-9b803005dd9a?code=KEKW5D&embed=true">未曾说喜欢 1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvif">完美骗子-01-06完结+番外</a>
            <a href="https://jjnztxsb.lanzov.com/b00g36cj9c ">无名花1-16完结</a>
            <a href="https://pan.baidu.com/s/167rV5fHlzlFLSIwoXrnP5A?pwd=bleh">无窗之屋</a>
            <a href="https://flowus.cn/seacomic2/share/ccb6a2de-35b7-4ec8-9e4e-3d2cdcc42db6?code=KEKW5D&embed=true">无法触及的大公1-11完结</a>
            <a href="https://pan.baidu.com/s/19fdYNuCUpNlqn1wARbA67g?pwd=bleh">吾乃本国王子 1-5 完结</a>
            <a href="https://flowus.cn/seacomic2/share/bb9030bb-2af9-46f0-8a71-82d94fe403d4?code=KEKW5D&embed=true">韦斯特马克效应1-7完结</a>
            <a href="https://pan.baidu.com/s/1baEqgvj-p_-e6Dp_YpVg1Q?pwd=bleh">我的 x 爸爸 1-18 完结外传 1-6 完结</a>
            <a href="https://pan.baidu.com/s/1X1HqWV6ewX7kgfZZYuVtGQ?pwd=bleh">我的梦中总是只有大叔出现 1-10 完结</a>
        </div>
        
        <div>
            <span class="a-title">X</span>
            <a href="https://flowus.cn/seacomic2/share/00287ce6-a763-4451-8d04-04cf0a4c9d5f?code=KEKW5D&embed=true">兄弟爱1-8完结</a>
            <a href="https://pan.baidu.com/s/1C_qTnxULqzd6gj3m6iiq1w?pwd=bleh">小郎君1-3完结</a>
            <a href="https://pan.baidu.com/s/1rRGUDngZyqY8T53m-ufQsw?pwd=bleh">现实爱人1-7+外传完结</a>
            <a href="https://pan.baidu.com/s/1-O7Kc9TFO2rKXOm_g5Afow?pwd=bleh">寻找单间（00-10完结）</a>
            <a href="https://flowus.cn/seacomic2/share/155fa60a-e864-458e-a938-2441396001eb?code=KEKW5D&embed=true">寻找辛德瑞拉1-18完结</a>
            <a href="https://pan.baidu.com/s/1qQMpPXzWZTn8hBmyDsXXAQ?pwd=kcef">信义诚信原则 无光版1-6完结</a>
            <a href="https://pan.baidu.com/s/17TeywoTM3eXlC562YcGGMg?pwd=bleh">寻找有经验的社畜 1-2完结</a>
            <a href="https://pan.baidu.com/s/14mt97VbckKEwKDXdjlCnlA?pwd=bleh">刑法第260条 台无光1-10完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2nom4d">选手必胜金牌猎人1-5完结</a>
            <a href="https://pan.baidu.com/s/1onGdfFKA3rEamPL8OCffOg?pwd=bleh">【理想家庭系列】兄弟隐藏的秘密1-5完结</a>
        </div>
        
        <div>
            <span class="a-title">Y</span>
            <a href="https://pan.baidu.com/s/1xsaRrR-5eHVWbSPNSpB0KA?pwd=bleh">有缘自会相遇1-8完结</a>
            <a href="https://pan.baidu.com/s/1fFGvDQaMkKJ62n6RH7UGEQ?pwd=bleh">炎火1-5完结</a>
            <a href="https://pan.baidu.com/s/1F5p1RNEOlCAZm9XPux-96A?pwd=bleh">yin乱电话 1-5 完结</a>
            <a href="https://pan.baidu.com/s/174EmZ9KpF6jytEZwghIgMQ?pwd=bleh">友爱的 xx 1-5 完结</a>
            <a href="https://flowus.cn/seacomic2/share/bf610863-582d-4f7c-adae-676d232257e2?code=KEKW5D&embed=true">影子怪物和新郎1-10完结</a>
            <a href="https://pan.baidu.com/s/1No483e8CH8Y5sQLceu7LDA?pwd=bleh">【理想家庭系列】以眼还眼1-5完结</a>
            <a href="https://pan.baidu.com/s/1boTn71n5u589QPtiC8rTZg?pwd=bleh">【理想家庭系列】永恒的情缘1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ag5kb">一步之遙 1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2uk8qj">易感期算什么1-7完结</a>
            <a href="https://pan.baidu.com/s/1Fgd80AN6CKSs8GpyUoDiOQ?pwd=bleh">love one night 一夜情 1-7 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rbbji">也和我交往吧1-5完结</a>
            <a href="https://flowus.cn/seacomic2/share/1a69da05-a7a5-4639-a0d5-1a60e0da91af?code=KEKW5D&embed=true">亡灵呼唤的宅邸/幽魂宅邸1-15完</a>
            <a href="https://pan.baidu.com/s/1n1rLGH3RIN1Cdl0obeK3Yw?pwd=bleh ">油脂商人的菊花滑溜溜-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2zl0qh">预备役集训中的冤家对头1-5完结</a>
            <a href="https://pan.xunlei.com/s/VOP_srv5Ijb-lTNxNiAZQ9x1A1?pwd=b6df#">于波涛汹涌的湖水中 1-10 完结</a>
        </div>
        
        <div>
            <span class="a-title">Z</span>
            <a href="https://pan.baidu.com/s/1WntL51w2WspS8tUwidC5CQ?pwd=bleh">再来一次吧！1-3完结</a>
            <a href="https://pan.baidu.com/s/1EzeGPD6NpKDT15xVOlKF2g?pwd=bleh">【理想家庭系列】致希愿 1-4完结</a>
            <a href="https://pan.baidu.com/s/1WntL51w2WspS8tUwidC5CQ?pwd=bleh">再来一次吧！1-3完结</a>
            <a href="https://pan.baidu.com/s/17HJdgmdLSNSw7e-wCG6U_A?pwd=bleh">正义女神蒙着眼【完结】</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx8b">正好是我讨厌的类型1-8完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qxbe">直到你破碎1-8完结</a>
        </div>
    </div>


    <!-- 内联 JavaScript -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const viewButton = document.getElementById('viewButton');
        const allLinks = document.querySelectorAll('a');
        const searchResult = document.getElementById('searchResult');

        // 实时搜索
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            let matchCount = 0;
            let firstMatch = null;

            allLinks.forEach(link => {
                const text = link.textContent.toLowerCase();
                const index = text.indexOf(searchTerm);
                
                // 清除之前的高亮
                link.innerHTML = link.textContent;

                if (searchTerm && index > -1) {
                    // 高亮匹配文字
                    const highlighted = link.textContent.substring(0, index) + 
                        `<span class="highlight">${link.textContent.substr(index, searchTerm.length)}</span>` +
                        link.textContent.substring(index + searchTerm.length);
                    link.innerHTML = highlighted;
                    
                    link.style.display = 'block';
                    link.parentElement.style.display = 'block';
                    if (!firstMatch) firstMatch = link;
                    matchCount++;
                } else {
                    link.style.display = searchTerm ? 'none' : 'block';
                }
            });

            // 显示搜索结果
            if (searchTerm) {
                searchResult.textContent = matchCount ? `找到 ${matchCount} 个结果` : '没有找到匹配内容';
            } else {
                searchResult.textContent = '';
            }
        });

        // 查看按钮点击事件
        viewButton.addEventListener('click', () => {
            const searchTerm = searchInput.value.trim().toLowerCase();
            if (!searchTerm) {
                searchResult.textContent = '请输入搜索内容';
                return;
            }

            const firstVisibleLink = document.querySelector('a[style="display: block;"]');
            if (firstVisibleLink) {
                firstVisibleLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                searchResult.textContent = '没有找到匹配内容';
            }
        });

        // 回车键触发查看
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                viewButton.click(); // 模拟点击查看按钮
            }
        });
    </script>
</body>
</html>
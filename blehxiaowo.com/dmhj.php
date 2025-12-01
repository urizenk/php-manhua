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
            $redis->setex('latest_password_id', 300, $latest_password_id); // 缓存5分钟
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
    <title>动漫合集</title>
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
        <h1><span class="title">动漫合集</span></h1>
        <div class="tip">
            Tip ：刷新后才能看到新增漫漫！
        </div>
        <a href="index.php" class="back-to-index"><i class="fas fa-arrow-left"></i>回到目录</a>

        <!-- 搜索框 -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="输入动漫名称搜索...">
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
            <a href="https://pan.quark.cn/s/fc91567b30a5">暗黑破坏神</a>
            <a href="https://pan.quark.cn/s/69ec7cc3611e">爱情可以分割吗</a>
            <a href="https://pan.quark.cn/s/305c410e9d24">暗杀教室</a>
            <a href="https://pan.quark.cn/s/6f2a2972ecb7">凹凸世界AUTO</a>
            <a href="https://pan.quark.cn/s/4f0457dda6fc">暗之末裔</a>
        </div>

        <div>
            <span class="a-title">B</span>
            <a href="https://pan.quark.cn/s/7435b08bd5ec">八犬传：东方八犬异闻</a>
            <a href="https://pan.quark.cn/s/35ce5a76dacf">兵部京介／绝对可怜小孩</a>
            <a href="https://pan.quark.cn/s/4d58a7c72c54">冰菓</a>
            <a href="https://pan.quark.cn/s/c1a9b3265d7c">伯爵与妖精</a>
            <a href="https://pan.quark.cn/s/f0f38aac06be">绊KIZUNA</a>
            <a href="https://pan.quark.cn/s/c46178c4082d">别人家的BL</a>
            <a href="https://pan.quark.cn/s/fcdea12cd2b0">冰上的尤里</a>
            <a href="https://pan.quark.cn/s/f56c65c946f9">宝石商人理查德</a>
        </div>

        <div>
            <span class="a-title">C</span>
            <a href="https://pan.quark.cn/s/9587464ead90">残次品</a>
            <a href="https://pan.quark.cn/s/996fc23f1ea5">出柜</a>
            <a href="https://pan.quark.cn/s/3ff0a72a190f">超级恋人super</a>
            <a href="https://pan.quark.cn/s/1c3c73617c4d">初恋怪兽</a>
            <a href="https://pan.quark.cn/s/874d0370b693">纯情罗曼史</a>
            <a href="https://pan.quark.cn/s/778dcc91b629">穿书指南</a>
        </div>

        <div>
            <span class="a-title">D</span>
            <a href="https://pan.quark.cn/s/4a00747b7af1">道格基里尔</a>
            <a href="https://pan.quark.cn/s/b4b1f0b85f45">大贵族</a>
            <a href="https://pan.quark.cn/s/90a464119f18">定海浮生录</a>
            <a href="https://pan.quark.cn/s/c63445cf9536">东京巴比伦</a>
            <a href="https://pan.quark.cn/s/757e08958bf9">刀剑乱舞</a>
            <a href="https://pan.quark.cn/s/8584b2de8aa3">电锯人</a>
            <a href="https://pan.quark.cn/s/fee730be0bff">大欺诈师</a>
            <a href="https://pan.quark.cn/s/4dc578142010">帝王攻略</a>
            <a href="https://pan.quark.cn/s/0a36505370ae">独占我的英雄</a>
            <a href="https://pan.quark.cn/s/24d123226264">大正小小先生</a>
        </div>

        <div>
            <span class="a-title">F</span>
            <a href="https://pan.quark.cn/s/d7c7e2e4a306">翡翠森林狼与羊／暴风雨之夜</a>
            <a href="https://pan.quark.cn/s/8e082c0f6dd0">富豪刑事</a>
            <a href="https://pan.quark.cn/s/7985a596a6f3">放课后的职员室</a>
            <a href="https://pan.quark.cn/s/ed6d9485f3bf">腐女子的品格</a>
            <a href="https://pan.quark.cn/s/cb068d5edba3">腐男子高校生活</a>
            <a href="https://pan.quark.cn/s/68f2a4dcdb1b">Free！男子游泳部</a>
            <a href="https://pan.quark.cn/s/40221ad426d7">富士见寒冷前线指挥官</a>
            <a href="https://pan.quark.cn/s/a840092fd872">风与木之诗</a>
        </div>

        <div>
            <span class="a-title">G</span>
            <a href="https://pan.quark.cn/s/ed9946c72d87">鬼灯的冷C</a>
            <a href="https://pan.quark.cn/s/d09741623480">怪H猫</a>
            <a href="https://pan.quark.cn/s/b58745bc95b9">GIVEN被赠与的未来</a>
            <a href="https://pan.quark.cn/s/20243b10ff1d">GIVEN反面的存在</a>
            <a href="https://pan.quark.cn/s/21df3c6493a3">公主公主</a>
        </div>

        <div>
            <span class="a-title">H</span>
            <a href="https://pan.quark.cn/s/7f57b6ce135f">海边的异邦人</a>
            <a href="https://pan.quark.cn/s/3e1cef3ae5ec">蝴蝶绮~年轻的信长</a>
            <a href="https://pan.quark.cn/s/8199d78bb9fc">黄昏失焦</a>
            <a href="https://pan.quark.cn/s/b26c714520e0">后街女孩</a>
            <a href="https://pan.quark.cn/s/baacccdd7372">黑色嘉年华／狂欢节</a>
            <a href="https://pan.quark.cn/s/8ae9b36ac8a6">火宵之月</a>
            <a href="https://pan.quark.cn/s/29c89bd095fb">黄油爱人</a>
            <a href="https://pan.quark.cn/s/091f41596267">幻影少年</a>
            <a href="https://pan.quark.cn/s/a77f16c059fb">会长大人是女仆</a>
            <a href="https://pan.quark.cn/s/6b26ab039016">黑执事 4更11</a>
        </div>

        <div>
            <span class="a-title">J</span>
            <a href="https://pan.quark.cn/s/bbe6deef9bf2">绝爱追缉令</a>
            <a href="https://pan.quark.cn/s/6d14e442ffc2">缉D特搜班</a>
            <a href="https://pan.quark.cn/s/09b20a50825d">极恶老大</a>
            <a href="https://pan.quark.cn/s/91d8f97a4bba">咎狗之血</a>
            <a href="https://pan.quark.cn/s/962847ddebc4">嫁给非人类／非人先生的新娘</a>
            <a href="https://pan.quark.cn/s/9c48af396595">即将长大成人</a>
            <a href="https://pan.quark.cn/s/6758f7b9f632">眷恋你的温柔</a>
            <a href="https://pan.quark.cn/s/c82542403047">教师F败方程式</a>
            <a href="https://pan.quark.cn/s/0983e3cf9b7f">今天开始做魔王</a>
            <a href="https://pan.quark.cn/s/dd4900743908">解药</a>
        </div>

        <div>
            <span class="a-title">K</span>
            <a href="https://pan.quark.cn/s/14dda983a715">K</a>
            <a href="https://pan.quark.cn/s/ab83ed8f7817">狂野情人</a>
            <a href="https://pan.quark.cn/s/84f7a7f6359c">口罩男子明明不想谈恋爱</a>
            <a href="https://pan.quark.cn/s/195894b44dc4">课长之恋</a>
        </div>

        <div>
            <span class="a-title">L</span>
            <a href="https://pan.quark.cn/s/3b27c498aae6">6Lover</a>
            <a href="https://pan.quark.cn/s/185e5e6db4d5">恋爱雏歌</a>
            <a href="https://pan.quark.cn/s/26eb495e56ba">恋爱舞台</a>
            <a href="https://pan.quark.cn/s/ad3a8e246764">鹿枫堂四色日和</a>
            <a href="https://pan.quark.cn/s/3223dd67ddae">烈火浇愁</a>
            <a href="https://pan.quark.cn/s/2db25eda1988">灵能百分百</a>
            <a href="https://pan.quark.cn/s/7722f960f78e">灵契</a>
            <a href="https://pan.quark.cn/s/debdf4cc85f0">历师</a>
        </div>

        <div>
            <span class="a-title">M</span>
            <a href="https://pan.quark.cn/s/9addaba01aa7">皿三昧</a>
            <a href="https://pan.quark.cn/s/5b6701fe34fb">Mignon</a>
            <a href="https://pan.quark.cn/s/2386166b12d1">魔界王子</a>
            <a href="https://pan.quark.cn/s/f108ca4ad937">美男高校地球防卫部</a>
            <a href="https://pan.quark.cn/s/462d0731950b">末日曙光</a>
        </div>

        <div>
            <span class="a-title">N</span>
            <a href="https://pan.quark.cn/s/e75e10b86c69">你来了</a>
            <a href="https://pan.quark.cn/s/12261b30d866">Number24</a>
        </div>

        <div>
            <span class="a-title">O</span>
            <a href="https://pan.quark.cn/s/f98e46e6cff4">偶像星愿</a>
        </div>

        <div>
            <span class="a-title">P</span>
            <a href="https://pan.quark.cn/s/fc68eaaecee4">漂亮爸爸</a>
        </div>

        <div>
            <span class="a-title">Q</span>
            <a href="https://pan.quark.cn/s/8cb0a6a83985">千铳士</a>
            <a href="https://pan.quark.cn/s/31885493a904">千百年物语</a>
            <a href="https://pan.quark.cn/s/4270e8002e42">强风吹拂</a>
            <a href="https://pan.quark.cn/s/6b03223a9d0d">棋魂</a>
            <a href="https://pan.quark.cn/s/23d3724de45e">奇迹列车</a>
            <a href="https://pan.quark.cn/s/dc8849a18be7">请叫我小熊猫</a>
            <a href="https://pan.quark.cn/s/345b59307fae">蔷薇王的葬列</a>
        </div>

        <div>
            <span class="a-title">S</span>
            <a href="https://pan.quark.cn/s/458d05bd3823">尸者帝国</a>
            <a href="https://pan.quark.cn/s/025f0b2b108c">三角窗外是黑夜</a>
            <a href="https://pan.quark.cn/s/053275bd5462">四月一日灵异事件簿</a>
            <a href="https://pan.quark.cn/s/c3f28852a10a">四周恋人</a>
            <a href="https://pan.quark.cn/s/d00d2d632057">时光代理人</a>
            <a href="https://pan.quark.cn/s/50862096b338">神幻拍档</a>
            <a href="https://pan.quark.cn/s/8547cbe5b79c">世界第一初恋</a>
            <a href="https://pan.quark.cn/s/68c8c38c062f">少年侦探团TRICKSTER</a>
        </div>

        <div>
            <span class="a-title">T</span>
            <a href="https://pan.quark.cn/s/a2060c3736e3">同级生</a>
            <a href="https://pan.quark.cn/s/1a0f6e842fd7">天使禁猎区</a>
            <a href="https://pan.quark.cn/s/82457c806cc4">天使之羽</a>
            <a href="https://pan.quark.cn/s/5ad30a2ff1bd">逃亡</a>
            <a href="https://pan.quark.cn/s/644ac91fcfde">田中君总是如此慵懒</a>
        </div>

        <div>
            <span class="a-title">W</span>
            <a href="https://pan.quark.cn/s/d39ef3880779">危险便利店</a>
            <a href="https://pan.quark.cn/s/8098a74dca77">无爱之战</a>
            <a href="https://pan.quark.cn/s/a0a5a3f6a235">我的狼人男友</a>
            <a href="https://pan.quark.cn/s/a539d97f46dc">我的新上司是天然呆</a>
            <a href="https://pan.quark.cn/s/9cc2f26ec2c2">无法逃离的背叛</a>
            <a href="https://pan.quark.cn/s/bda74b7c9cda">文豪野犬 5季全</a>
            <a href="https://pan.quark.cn/s/17ead2ab7fc4">文豪野犬汪</a>
            <a href="https://pan.quark.cn/s/9564fa07b761">我家大师兄脑子有坑</a>
            <a href="https://pan.quark.cn/s/f626487a4e54">我开动物园那些年</a>
            <a href="https://pan.quark.cn/s/fb1587617f2c">未来都市</a>
            <a href="https://pan.quark.cn/s/6dbfab8a0828">我让最想被拥抱的男人给威胁了+西班牙篇</a>
            <a href="https://pan.quark.cn/s/b3f765a104f9">武士弗拉明戈</a>
            <a href="https://pan.quark.cn/s/e4c75d410956">无限滑板</a>
            <a href="https://pan.quark.cn/s/a23cbf29209c">网中鱼</a>
            <a href="https://pan.quark.cn/s/36fc27a6d996">喂，看见耳朵啦</a>
        </div>

        <div>
            <span class="a-title">X</span>
            <a href="https://pan.quark.cn/s/042b4dc0f13f">嘻嘻嘻嘻嘻血鬼 1-12完结</a>
            <a href="https://pan.quark.cn/s/3ad5ec645222">喜欢就是喜欢</a>
            <a href="https://pan.quark.cn/s/7fbce9f43652">星际幻爱</a>
            <a href="https://pan.quark.cn/s/9cc1af4fa636">戏剧性谋S</a>
            <a href="https://pan.quark.cn/s/0f9e11e3cb56">心跳无限次</a>
            <a href="https://pan.quark.cn/s/e43e1ad9b978">吸血鬼仆人</a>
            <a href="https://pan.quark.cn/s/24b9867ac069">弦音凤舞高中弓道部</a>
            <a href="https://pan.quark.cn/s/d98e923cfd3e">西洋古董洋果子店</a>
            <a href="https://pan.quark.cn/s/2f0071e7e605">学院天堂</a>
            <a href="https://pan.quark.cn/s/7a0132c251da">X战记</a>
            <a href="https://pan.quark.cn/s/1f0dc12566eb">血咒圣痕</a>
        </div>

        <div>
            <span class="a-title">Y</span>
            <a href="https://pan.quark.cn/s/e16622f88a39">妖怪公寓的幽雅日常</a>
            <a href="https://pan.quark.cn/s/f273a85e8476">翼年代记</a>
            <a href="https://pan.quark.cn/s/55278832f3ed">语义错误</a>
            <a href="https://pan.quark.cn/s/42287fce9346">忧郁的物怪庵</a>
            <a href="https://pan.quark.cn/s/3a6f8af80480">吟游默示录</a>
            <a href="https://pan.quark.cn/s/eb6e56f119ea">炎炎消防队</a>
            <a href="https://pan.quark.cn/s/006ea03af6c1">炎之蜃气楼</a>
        </div>

        <div>
            <span class="a-title">Z</span>
            <a href="https://pan.quark.cn/s/6ddc347746b7">终结的炽天使</a>
            <a href="https://pan.quark.cn/s/93ae9775513a">战栗杀机／香蕉鱼</a>
            <a href="https://pan.quark.cn/s/384a1fea248b">这名男子</a>
            <a href="https://pan.quark.cn/s/b7508e20e13e">昨日青空</a>
            <a href="https://pan.quark.cn/s/aa74e8e62ae7">佐佐木与宫野</a>
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
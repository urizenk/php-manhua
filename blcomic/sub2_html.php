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
    <title>韩漫合集</title>
            <!-- 引入 FontAwesome -->
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

        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 浅橙色背景 */
            padding: 8px 12px; /* 内边距 */
            border-radius: 25px; /* 圆角 */
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            display: inline-block; /* 使背景框仅包裹文字内容 */
            max-width: 100%; /* 防止超出容器宽度 */
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
                        /* 回到首页按钮样式 */
        .back-to-index {
            display: inline-flex;
            align-items: center;
            gap: 8px; /* 图标和文字之间的间距 */
            background-color: #EC5800; /* 绿色背景 */
            color: white; /* 白色文字 */
            padding: 10px 15px;
            border-radius: 5px; /* 圆角矩形 */
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* 与下方内容的间距 */
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
        <!-- 页面标题 -->
        <h1><span class="title">韩漫合集</span></h1>
                <!-- Tip 标签 -->
        <div class="tip">
            Tip ：单部漫的密码就是每日访问码，一码通用喔！刷新后才能看到新增漫漫！
        </div>
                        <!-- 回到首页按钮 -->
        <a href="index.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i> <!-- FontAwesome 向左箭头图标 -->
            回到目录
        </a>
        
        <!-- 新增搜索框和查看按钮 -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="漫名不用打全称，用关键词搜索...">
            <button id="viewButton">查看</button>
            <div class="search-result" id="searchResult"></div>
        </div>
        
        <div>
            <span class="a-title">新增漫</span>
            <div>
                </p>
                <a href="https://jjnztxsb.lanzov.com/b00g3rth4d">宗家破坏者</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3rr7ob">Hit me hard</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3rnpcd">你是我的全世界-台无光</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3r2d5i">十年</a>
                <a href="https://flowus.cn/seacomic2/share/8beab38c-76a3-4e52-87a9-09ee48747174?code=KEKW5D&embed=true">薄荷恋上糖</a>
                <a href="https://flowus.cn/seacomic2/share/e2dc4a84-9f7e-4a88-8437-382378fd7e85?code=KEKW5D&embed=true">斜汉/思寒</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3qwsvi">猫咪疗愈/猫咪疗法</a>
                <a href="https://flowus.cn/seacomic2/share/eeefeb9a-1214-4ca6-ac3f-561af78e4117?code=KEKW5D&embed=true">迷惑的境界 无光版</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3qvx9a">缪思 台无光</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3qhead">倦怠警报/倦怠预警</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3q6n4f">辣椒失踪事件/GG失踪事件</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3q6ngh">半地下室的男人被关在我的房间里</a>
                <a href=" https://jjnztxsb.lanzov.com/b00g3q6nja">捕鼠器</a>
                <a href="https://flowus.cn/seacomic2/share/be8c43b7-eff5-4e06-a939-39d21ca2d0c0?code=KEKW5D&embed=true">追星之恋 台无光</a>
                <a href="https://flowus.cn/seacomic2/share/f8283220-89ab-4b99-a8b5-a3fc9b7e82b9?code=KEKW5D&embed=true">白莲绽放的温度-台无光</a>
                <a href="https://jjnztxsb.lanzoum.com/b00g3q1nwd">Biscuit</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3pfo1g">特殊任务，男人的挑战</a>
                <a href="https://jjnztxsb.lanzov.com/b04edz4bc">公私分明</a>
                <a href="https://jjnztxsb.lanzov.com/b00g3p7rte">Happy twogether</a>
                <a href="https://flowus.cn/seacomic2/share/bf02cf8a-5094-43f4-8695-325f2e870827?code=KEKW5D&embed=true">红线挑战/红线任务 无光版</a>
            </div>
        </div>
        
       <div>
            <span class="a-title">铁粉福利</span>
            <h4>【码见铁粉群】</h4>
            <div>
                </p>
                <a href="https://pan.baidu.com/s/18Y6Q5Q5dxlMNMmqU9yEY4g">不会吃亏的恋爱 无光版1-7</a>
            </div>
        </div>

        <span class="a-title">A</span> <!-- 【A】标签变成橙色背景 -->
        <!-- 连载中 -->
        <h4>连载中</h4>
        <div>
            <a href="https://jjnztxsb.lanzov.com/b00g322ufa">黯缘 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv2je">ANAN</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6x9c">爱情是幻想/恋爱幻想曲</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee77gd">爱情魔咒</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee77pc">肮脏的XX</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2odezg">alpha创伤</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38q11c">爱情来袭</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ek7yf">Artsmanz 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ia1xc">爱欲_台版</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3a3wef">Amber alert安珀警报</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3am11a">A先生的农场/Mr. A's Farm 台无光</a>
        </div>
        
        <!-- 完结 -->
        <h4>完结</h4>
        <div>
            <a href="https://jjnztxsb.lanzov.com/b04ef6r9g">阿罗莎之花[季结]</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2of2qh">艾斯德的庭院1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g37e35a">阿加托医生报告 </a>
            <a href="https://pan.baidu.com/s/1D7xjWb12fXr_-aZg7AgxFw?pwd=bleh">ACT OUT1-4完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee77tg">傲慢的圣托一世</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee77jg">暧昧的边缘</a>
            <a href="https://flowus.cn/seacomic2/share/a8f6cbf3-4f99-4460-8be4-0f24673a333f?code=KEKW5D&embed=true">安慰剂 无光版正篇58+外传10完结</a>
        </div>

        <!-- B 标签模块 -->
        <div>
            <span class="a-title">B</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04ee76xe">不道德</a>
            <a href="https://flowus.cn/seacomic2/share/e3072fb9-ccc2-42ff-8fcb-7c4100d215e0?code=KEKW5D&embed=true">薄荷糖</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2gapne">blaze out</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2ltync">不幸人生</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2pdqaj">菠菜花环</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3clzba">不完全燃烧</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29rdkj">波涛的海岸</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3buzxa">Bad Apple 台无光</a>
            <a href="https://flowus.cn/seacomic2/share/eb50411a-fb95-490e-a2f4-1a5bfcfa84e5?code=KEKW5D&embed=true">不正常恋爱/负面恋情 无光版</a>
            <a href="https://jjnztxsb.lanzov.com/b00g254f1a">BackLight背光 台无光</a>
            <a href="https://flowus.cn/seacomic2/share/5c054c75-a4b9-474b-9505-f8a38772009d?code=KEKW5D&embed=true">不要对我说谎 台无光 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g2m4e7c">棒球、bad-not-bat</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3mxn0j">不想看我发疯就给我生个小豹猫</a>
            <a href="https://jjnztxsb.lanzov.com/b00g33uqvg">不行啦！不知道？其实知道吧！？台无光</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b00g2bwycj">帮助</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2h5dte">BJAlex</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee774b">管家</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee76md">败类完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee76qh">变温动物完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2peb2h">变好吃吧 嘿!♡</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2k94ng">不要招惹小狗</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee76ij">boy meets girl完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29sraj">别介意亲爱的</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2m1y0h">不要打破玻璃</a>
            
            <a href="https://jjnztxsb.lanzov.com/b04ee772j">不是我喜欢的类型啊？</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee770h">不及他人的关系/越界死党/恋人未满</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2slxsf">百万罗曼史完结+外传完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef3cnc">巴尔道的宫52end</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5k3c">纽约城-Bigapple完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5sif">身体情结Bodycomplex完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5sqd">部长，您辛苦了完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ykybe">不屈的冠军1-22完结+外传1-3完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efffti">眼罩游戏BlindPlay</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2jeq0j">beta小伯爵的重生</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2k99sb">捕到的鲸鱼不给喂饭</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2kfg">BELIEVE MY SIGN 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee76ta">别有用心的恋爱史/未必故意的恋爱史</a>
        </div>
        
                <!-- C 标签模块 -->
        <div>
            <span class="a-title">C</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04ee5gxc">彩虹城</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2u6v9g">彩虹甜甜圈</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3bk4wd">从艾萨克开始</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3e3k3g">错误探索领域</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2l151i">Control Time 台无光</a>
            <a href="https://pan.baidu.com/s/1o184I6514_r7wb8XSf2NSQ?pwd=bleh">COLOSSAL 单行本</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2lop1g">错误的恋爱方式</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rzoja">成为被我抛弃之人的奴隶</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04ef5wvc">窗外的窗</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef3cih">初恋情结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtvwj">村舍花园</a>
            <a href="https://flowus.cn/seacomic2/share/870d5e8d-8c64-4dbc-bcd5-cbce0115b0d2?code=KEKW5D&embed=true">偿还payback完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef352j">陈秘书监禁日记</a>
            <a href="https://flowus.cn/seacomic2/share/c0c20b1d-a7c7-41ef-ad12-8451517fb404?code=KEKW5D&embed=true">从我家滚出去</a>
            <a href="https://pan.baidu.com/s/1j_WRI2QKZZyQ6SasrmHtNg?pwd=bleh">潮湿的沙漠 第一季完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5xsf">沉迷社长大人完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef313g">春风烂漫第一季完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef3ced">才不是樵夫呢!（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5m0b">纯gay培训所（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5ref">耻R应用程序（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b00g37y4ri">Come and Take 1-66完结</a>
        </div>
        
                        <!-- D 标签模块 -->
        <div>
            <span class="a-title">D</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3gja9a">D-8</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2n2hkh">毒素</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2dvd6d">Dear.00 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ieu2j">DASH</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3j4gaf">单恋必胜法</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3ij91i">Diss love冒犯爱情 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g3cjc2b">电吉他和领带</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5pcb">当心三月十五</a>
            <a href="https://jjnztxsb.lanzov.com/b04ed5rih">代号Anastasia</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ew3sh">颠倒的甲乙关系 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef34ta">当杀手坠入爱河</a>
            
        </div>
        
        <h4>完结</h4>
        <div>    
            
            <a href="https://jjnztxsb.lanzov.com/b00g2ty39a">Dearest</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ltxqj">DreadfulNight恐怖之夜</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2l57be">大叔，你什么时候来大学？</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef37ih">单恋（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef38bg">堆栈溢出完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ew6pc">Drivers high</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2d9ibg">第一诫命</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef390b">地铁环线（完）</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz3mh">定义关系 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2osl3g">倒霉的丘比特信使</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef632f">第三种结局（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt2j">颠覆之血/覆血难收</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef365i">Deardoor亲爱的门（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04em8nda">大公阁下的玩物（无圣光版）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef382h">大公阁下的玩物playingthing（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef38rc">殿下的撩夫日常／你爸爸是谁（完结）</a>
        
        </div>
        
                                <!-- E 标签模块 -->
        <div>
            <span class="a-title">E</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2559qf">2020</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3clyyh">恶作/恶作剧</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef3aif">恶魔的低语57</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2zpayj">恶人谈</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef397i">恶缘的发现（完）</a>
            <a href="https://jjnztxsb.lanpv.com/b00g2ls4qj">恶魔也疯狂一起来rock</a>
        </div>
                                <!-- F 标签模块 -->
        <div>
            <span class="a-title">F</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04edz3xi">犯规</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2warrg">翻转</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ew5ab">Flash light</a>
            <a href="https://jjnztxsb.lanzov.com/b00g24zl8d">腐蚀人</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2n5b6f">负面恋情</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3n0f4d">Full book</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2awpna ">仿金 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29rdod">弗林的狐狸饮料</a>
            <a href="https://jjnztxsb.lanzov.com/b00g25sxde">付出一切的男人</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29rcra">Feel my Benefit</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz43e">非零和博弈</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2fs4uh">蜂蜜味的S级向导</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04ef6mah">逢九</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29ujyh">开始犯规 /犯规开始【季节】</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbz1c">仿佛来到了异世界</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2g7lva">弗雷呀</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6ohg">疯狂之地</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt2j">颠覆之血/覆血难收</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef69cb">发晴应用（完）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef65ni">复读生（完结）</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7wle">房号1305 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2lyz5a">From naughty1-11完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6deh">放学后的保健室 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2of2xe">父亲的玩具</a>
        </div>

                                <!-- G 标签模块 -->
        <div>
            <span class="a-title">G</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04edz4vc">鬼夜曲</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz4bc">公私分明</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2iy9of">狗与飞鸟</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2sgx7e">哥的omega</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2gsguf">骨与花瓣</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6scf">共享关系</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz4of">鬼门关杀</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3aozpa">公共财爸爸 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38e29e">改过自新的余地/悔改的余地</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ek9xg">关于我爱你的这件事 台版</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2hyn7g">鬼夜曲台版</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b00g2vfhng">狗毛情缘</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29ekra">甘雨警报/danbi</a>
            
            <a href="https://jjnztxsb.lanzov.com/b04ef6ppa">哥哥的恋人</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6lqh">勾引Alpha的方法</a>
        </div>

                                <!-- H 标签模块 -->
        <div>
            <span class="a-title">H</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04ef72ud">昏梦</a>
            <a href="https://jjnztxsb.lanzov.com/b00g391dhg">魂火</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3la5cj">虎虎相争/虎虎相搏</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3p571e">虎鲸宅邸</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2xhn5i ">红线任务【韩版】</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2r854j">虎鲸狩猎法</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2r53ej">画布上的油彩</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz5li">HoneyTrouble 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef70wd">划破黑夜的黎明</a>
            <a href="https://jjnztxsb.lanzov.com/b04edz5if">海平面上的琶音</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ykzze">化身为狂攻的棉花团子</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt0h">花凋落的池塘/落花池</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6tba">Honeybear亲爱的熊</a>
            
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04edv48f">虎穴</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6uid">黄金搭档</a>
            <a href="https://jjnztxsb.lanzov.com/b04efam3e">黄龙传完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6usd">狗狗的恋爱/糊涂关系</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef6zlg">红色糖果完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efajad">虎视眈眈完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef71yb">坏孩子好搭档</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef70de">High clear守望者</a>
            <a href="https://jjnztxsb.lanzov.com/b04effkkj">狐神的秘密婚姻</a>
        </div>

                                <!-- I 标签模块 -->
        <div>
            <span class="a-title">I</span>
            <a href="https://jjnztxsb.lanzov.com/b00g29rcve">illusion</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29rd1a">into the thrill</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7tqb">Instant Family速成家庭</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ls4mf">in the pool</a>
        </div>
        
                                        <!-- J 标签模块 -->
        <div>
            <span class="a-title">J</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g29rayf">解毒剂</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7ooj">剑与花</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7q1i">救赎令</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2k94tc">尽管如此</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ti9ri">缄默法则</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2sdvuh">祭品丈夫 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3f60ah">解锁安乐监禁</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7pij">进入玫瑰园</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3e3hza">绝对复仇宣言 台无光 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g2nrppa">记忆的漫反射</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://flowus.cn/seacomic2/share/2e9b9ebf-e435-4703-bf81-9d6160fe75e9?code=KEKW5D&embed=true">将杀</a>
            <a href="https://jjnztxsb.lanzov.com/b00g39akte">教授的双重生活 完结 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g2fuyhg">坚强的去爱，拿下修吾</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7p5g">即使不爱我</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7sxc">江家的伊秀</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7g1i">金星的轨迹完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7ngf">交换／替身 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7fde">今天也下雨 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7gzc">姜秘书与少爷完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef78ba">金代理的秘密完结</a>

        </div>

                                        <!-- K 标签模块 -->
        <div>
            <span class="a-title">K</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3hr8ng">凯西的秘密 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g2y04wf">Kiss me if you can</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29ujyh">开始犯规 /犯规开始</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ltxqj">Dreadful Night恐怖之夜</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04efa1di">开始</a>
            <a href="https://jjnztxsb.lanzov.com/b04efafuj">狂攻系列</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef7xpe">狂热完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef9mte">狂犬完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa29a">开或关on-or-off</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef9zoh">可以的人熟悉的人</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef9nmd">狂乱列车/疯狂列车</a>
            <a href="https://jjnztxsb.lanzov.com/b04eff4qj">Kissmelier吻我骗子 完结</a>
            <a href="https://pan.baidu.com/s/18fCcAVWjCiNR-m8d-5jfiw?pwd=bleh">落张不入</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ko45i">猎人一夜要十次</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2jeudg">利马症候群、利马综合征</a>
        </div>

                                        <!-- L 标签模块 -->
        <div>
            <span class="a-title">L</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g2pm0uh">0cm</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ekala">临界点</a>
            <a href="https://flowus.cn/seacomic2/share/95e566c5-fcc3-4eee-885e-5f12b6f48645?code=KEKW5D&embed=true">利率50%</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3add1i">恋爱狙击</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3nd1ng">浪漫启示录 无光版</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2n7i9c">恋之录 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3dk08h">Love all play</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa8ah">伦敦之夜</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbz7i">两人独奏</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3hjbib"> love peace crazy</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2g4qja">恋爱治愈剂 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g33k66b">立场改变了吗？</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2fg5xe">邻居的私生活</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv6dc">连翘花落之处</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv62b">梨花绽放之恋</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3bxvub">老虎的生日年糕 </a>
            <a href="https://flowus.cn/seacomic2/share/01763b66-f73f-4f47-b2ce-3d86e9d2c6fd?code=KEKW5D&embed=true">垃圾也曾是新的</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2l2mej">流星划过宇宙 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbz5g">狼族饲养日记</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv78d">罗曼蒂克甜心队长</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2eka2b">黎明云彩河流</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt0h">花凋落的池塘/落花池</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mkq6j">Left Fluke Public School 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2nfsyf">little bitpsycho/有一点神经质</a>
            
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04edv7id">绿色镌像</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee77xa">劣势</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv5be">来自深渊</a>
            <a href="https://jjnztxsb.lanzov.com/b00g30pkgh">蓝色流沙完结</a>
            <a href="https://caiyun.139.com/w/i/2prALRjpLUdu5">龙之秘堡</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mtw3g">猎人只想安静生活</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv5pi">老板的小宝贝/BBB</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mwz9i">璃龙的伴侣</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa61g">邻居是公会成员</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa5uj">恋爱禁止的区域</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa5kj">林与海/金色海洋</a>
            <a href="https://jjnztxsb.lanzov.com/b04efa70b">恋爱阶级/恋爱上分</a>
            <a href="https://pan.baidu.com/s/18fCcAVWjCiNR-m8d-5jfiw?pwd=bleh">落张不入</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ko45i">猎人一夜要十次</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2jeudg">利马症候群、利马综合征</a>
            <a href="https://flowus.cn/seacomic2/share/fcc384db-b9cd-4277-ae77-5cea3c59c221?code=KEKW5D&embed=true">恋爱幻想/爱情是幻想 正篇+外传全＋Queen篇</a>
        </div>
        
                 <!-- M 标签模块 -->
        <div>
            <span class="a-title">M</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3gdx9c ">MIX-UP 台无光 </a>
            <a href="https://jjnztxsb.lanzov.com/b04eddive">魔咒Jinx</a>
            <a href="https://flowus.cn/seacomic2/share/dbe7c12f-0ff9-4671-948f-8ed43c5ae437?code=KEKW5D&embed=true">蜜桃少年/蜜桃男孩 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04efaire">魔界之月</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbdra">秘密关系</a>
            <a href="https://jjnztxsb.lanzov.com/b04ffqpcd">秘密恋爱</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2mzvpi">魔王谋逆</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbhcj">Mary Jane</a>
            <a href="https://jjnztxsb.lanzov.com/b04efarri">梅花树荫下</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3bsq2d">玫瑰宅邸的道勋先生</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3am11a">A先生的农场Mr. A's Farm 台无光</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b00g2emtlg">Maison</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbgbc">麦格芬</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyf4f">牡丹香</a>
            <a href="https://jjnztxsb.lanzov.com/b04efarzg">命中注定</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbekj">玫瑰与香槟</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbfmh">没有你的世界</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2gsgoj">Milky star</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbdxg">明泰小子卷死他</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2b2tah">猫狗狼的三角关系</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv7xi">没有麦克风也能听见</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbsjc">没理由／背叛的理由</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5ilc">无动作minimotion</a>
            <a href="https://pan.baidu.com/s/1XJ8jkUdVvQ0PTv_04H_OjA?pwd=bleh ">毛骨悚然？酸酸甜甜！ 正篇25+外传14完结</a>
        </div>
        
                         <!-- N 标签模块 -->
        <div>
            <span class="a-title">N</span>
            <a href="https://jjnztxsb.lanzov.com/b00g3oabne">逆煞</a>
            <a href="https://jjnztxsb.lanzov.com/b00g35f4xi">name-me</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2i2tfe">男孕台版 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbq2d">虐美人外传B 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbr5c">逆攻/反攻</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbrzc">你的跟踪狂</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbzlg">难以理解完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbrvi">那个alpha的秘密</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbu0f">NOTBAD/notnbad</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbmfc">男孕完结/浩植的故事</a>
            <a href="https://jjnztxsb.lanzov.com/b04efby6f">男医生与男护士完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef5k5e">纽约城-Bigapple完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2gsj1e">浓于血的向导_台版无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2iu1ed">浓于血的向导-长篇</a>
        </div>

                 <!-- O 标签模块 -->
        <div>
            <span class="a-title">O</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2ek95i">ONWARD</a>
            <a href="https://flowus.cn/seacomic2/share/3ebcfe96-4e7a-4ad9-8878-d0173b62c0b7?code=KEKW5D&embed=true">Off leash</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv3yf">omega情节</a>
            <a href="https://flowus.cn/seacomic2/share/b3c265e7-646b-4787-ace4-c501d4b02d0e?code=KEKW5D&embed=true">Omega沦陷报告1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbowb">偶然成为朋友</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyfde">偶然与必然之间</a>
        </div>
        
                 <!-- P 标签模块 -->
        <div>
            <span class="a-title">P</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3caywj">破晓</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2a6z7i">偏离轨道</a>
            <a href="https://jjnztxsb.lanzov.com/b00g26mv7g">Perle珍珠</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbxib">偏偏对我冷漠</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3a6ada">皮肤的温度</a>
            <a href="https://jjnztxsb.lanzov.com/b00g373cyh">Passion：raga</a>
            <a href="https://jjnztxsb.lanzov.com/b04edvaib">受难曲passion</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbxfi">破碎的爱情形态</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5azi">披萨外卖员与黄金宫</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04efbwbi">PAID 肉偿</a>
            <a href="https://pan.baidu.com/s/1mdzGRYDzP_QJMlTATf8MtQ?pwd=bleh"> Pocket7 1-5完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbxle">培育beta完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbwmj">扑通扑通临床试验</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbyla">配合星期一的恋人</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbwvi">普通向导的普通日常</a>
        </div>
        
         <!-- Q 标签模块 -->
        <div>
            <span class="a-title">Q</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g36unwh ">青孀驸马 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2bflah">前列腺报告书</a>
            <a href="https://jjnztxsb.lanzov.com/b00g31moeh">起源之祭/祈愿之祭</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2qzlhe">球童战术</a>
            <a href="https://jjnztxsb.lanzov.com/b00g27pkhe">禽兽之域</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdfah">拳拳醉爱</a>
            <a href="https://jjnztxsb.lanzov.com/b04ffyw2d">器物的世界</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3ocwnc">强降雨警报</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2pgxti">气味的边界/香气的界限</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyg0h">青苹果乐园</a>
            <a href="https://jjnztxsb.lanzov.com/b00g263v0f">亲爱的泰迪熊</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b00g263v7c">轻浮的XX先生</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2bnrkb">青蟹</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdduf">圈套-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdfli">情劫难逃</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2qm4mj">敲诈之恋 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdjub">青春颂完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdebc">请教教我-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv82d">囚徒驯养-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdlle">奇怪的梦境-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efddej">奇怪的邻居完结</a>
            <a href="https://flowus.cn/seacomic2/share/54cc2d33-8a35-4284-9c05-29626286fc3c?code=KEKW5D&embed=true">七日八色完结</a>
            <a href="https://flowus.cn/seacomic2/share/ce5b3c79-4ec7-4d26-bfbf-c1e546aec15c?code=KEKW5D&embed=true">轻易陷入三千浦1-21完结</a>
            <a href="https://flowus.cn/seacomic2/share/7b7047ae-e983-45be-900f-c559b5d9ca0b?code=KEKW5D&embed=true">心诚祈愿/祈祷</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdg7a">清洁百分百-完结</a>
            <a href="https://pan.baidu.com/s/1jjqoN0CZ8wu49DdRH7oXzQ?pwd=bleh">七个星期天1-22完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efddid">请把我变弯-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdt2d">请给我个孩子吧</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdmdc">前男友报告-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efbxve">朴汉浩的助理_完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdelc">其色鬼/被色鬼纠缠的人完结</a>
        </div>

         <!-- R 标签模块 -->
        <div>
            <span class="a-title">R</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g35l57e">融冰曲线 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgskb">日常兼职</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ic0xi">人鱼之沼 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g30wp9c">如此可爱的你</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3iy9pc">如此可爱的你/如此讨人喜爱的你 无光版</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdsmh">热症</a>
            <a href="https://jjnztxsb.lanzov.com/b04er09bg">日昇之屋</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://jjnztxsb.lanzov.com/b04efe5jc">热情的家伙</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdm3c">如履薄冰-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdmxc">人鱼传说-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efe2sd">日元的狗-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdq1e">如何成为家人-完结</a>
            <a href="https://pan.baidu.com/s/1YbMsA-be_ti51nL-R3VZZA?pwd=bleh">ron计划1-5完结</a>
            <a href="https://pan.baidu.com/s/1ZUk3_rrvNeUoWrxbhlI6OQ?pwd=bleh">若被龙尾缠绕1-6完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efe30b">如此喜欢我的话-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efe40h">如此讨厌我的话-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdpqd">人类行为矫正教育_1-6完结</a>
        </div>
        
                 <!-- S 标签模块 -->
        <div>
            <span class="a-title">S</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3olbbi">蛇窟/蛇洞</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3ecoab">三八</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2o2yni">3月/三月</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2et5oh">深渊-绝望</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2hximf">湿点</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2et5oh">深渊</a>
            <a href="https://jjnztxsb.lanzov.com/b04edynod">娑诃</a>
            <a href="https://jjnztxsb.lanzov.com/b00g26iuji">丧魂</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3kmg7g">顺遂的人生</a>
            <a href="https://flowus.cn/seacomic2/share/818f5aa2-cadc-4d4b-84cc-1f1d89b6b591?code=KEKW5D&embed=true">闪耀的他/闪闪发光的宝贝</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3fxv6b">神结 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ubp6h">十八岁的床</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2vv5ah">Sweet shot</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2wemve">Sugar trap</a>
            <a href="https://jjnztxsb.lanzov.com/b04edva7a">十二月</a>
            <a href="https://jjnztxsb.lanzov.com/b04efkash">水边之夜</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38yepc">说话的Business/话术型商务</a>
            <a href="https://jjnztxsb.lanzov.com/b00g311ouh">圣母怜子图</a>
            <a href="https://flowus.cn/seacomic2/share/818f5aa2-cadc-4d4b-84cc-1f1d89b6b591?code=KEKW5D&embed=true">闪耀的他</a>
            <a href="https://jjnztxsb.lanzov.com/b04edv9zc">失能开关</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5q9i">疏导障碍</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2eni">双重陷阱</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2k6h">失重罗曼史</a>
            <a href="https://jjnztxsb.lanzov.com/b04ffqy9e">书呆子计划</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2bflbi">舒适的痴迷</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38q0ni">Stage Behind幕后</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2m4dxc">second deal</a>
            <a href="https://jjnztxsb.lanzov.com/b04edvaib">受难曲passion</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyfpg">wetsand湿沙</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2ane">世上没有坏狗狗</a>
            <a href="https://jjnztxsb.lanzov.com/b04efflch">修车危情shutline</a>
            <a href="https://jjnztxsb.lanzov.com/b00g34pk8d">妖精的山枝梦/山枝鬼怪之梦</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2gvb8b">刷卡还是现金/卡还是现金</a>
            
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://flowus.cn/seacomic2/share/837d0663-6e81-4050-a6e3-8a1271a4a5cb?code=KEKW5D&embed=true">素描</a>
            <a href="https://pan.baidu.com/s/1A6fNzonLeybmUS1zt-MzxQ?pwd=bleh">食后景【第一季完结】</a>
            <a href=" https://jjnztxsb.lanzov.com/b00g35vabi ">水槽/水箱 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef31mf">失眠症</a>
            <a href="https://jjnztxsb.lanzov.com/b04efajpi">身体情结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g37rl6h">Twin guide</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyn6f">四周恋人</a>
            <a href="https://jjnztxsb.lanzov.com/b04efds7c">三三的家</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdo1c">是否订阅</a>
            <a href="https://jjnztxsb.lanzov.com/b00g274rkd">杀戮关系</a>
            <a href="https://jjnztxsb.lanzov.com/b04edyn3c">水平落下</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2jfa">社内恋爱</a>
            <a href="https://pan.baidu.com/s/1BioruTSOLzuQ3WnO9U-Gtg?pwd=bleh">S.O.S台无光完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2i0fib">神的男人1-4完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29qx2f">私密通话1-10完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2d57gb">使用后不能退货</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kkz6b">深入青陆之源</a>
            <a href="https://jjnztxsb.lanzov.com/b04efduli">狩猎游戏-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef3csh">士麦那与卡普里</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdxxi">少爷与秘书-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g35of0f">圣诞节的诅咒1-4完结外传1-8完结 </a>
            <a href="https://jjnztxsb.lanzov.com/b04efe2gb">4又2分之1停车场-完结</a>
        </div>
                 <!-- T 标签模块 -->
        <div>
            <span class="a-title">T</span>
            <h4>连载中</h4>
            
            <a href="https://jjnztxsb.lanzov.com/b00g2dotgh">投射</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2yoshi">特写</a>
            <a href="https://flowus.cn/seacomic2/share/cfafb93b-92ee-4c1d-805a-4c0b1287fed1?code=KEKW5D&embed=true">兔子洞 台无光</a>
            <a href=" https://jjnztxsb.lanzov.com/b00g3ksl8d ">TOY DADDY台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3mxn5e">替罪羊</a>
            <a href="https://jjnztxsb.lanzov.com/b04efeegd">探索战</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2xlu7g">同志地狱</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rb37i">天堂之上</a>
            <a href="https://jjnztxsb.lanzov.com/b04efegod">太阳之花</a>
            <a href="https://jjnztxsb.lanzov.com/b04efef0d">太阳骤雨</a>
            <a href="https://jjnztxsb.lanzov.com/b04efeigh">太阳的痕迹</a>
            <a href="https://jjnztxsb.lanzov.com/b04efe9qd">头发很敏感</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2vrouh">糖果yumyum</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3ggqih">吞下石榴的蛇 </a>
            <a href="https://jjnztxsb.lanzov.com/b00g322rkh">同一居室的助教</a>
            
            <a href="https://jjnztxsb.lanzov.com/b00g37jvmf">太阳班幼儿园同学 台无光</a>

        </div>
        
        <h4>完结</h4>
        <div>   
            <a href="https://jjnztxsb.lanzov.com/b04efedxe">糖果蜜雨</a>
            <a href="https://jjnztxsb.lanzov.com/b04edvave">特殊交易</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5gih">同情的形态</a>
            <a href="https://jjnztxsb.lanzov.com/b04enened">逃离鲨鱼的办法</a>
            <a href="https://pan.baidu.com/s/1KPQSIi_b9ty5JnFi31X7Nw?pwd=bleh ">同类1-10完结</a>
            <a href="https://pan.baidu.com/s/1CS74pZoBIwLYaCA9KIJXtQ?pwd=bleh">糖分成瘾 台无光1-10完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efedfg">甜而不腻第一季完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efe0tc">Thirsty渴望第二季完结</a>
        </div>
        
                         <!-- U 标签模块 -->
        <div>
            <span class="a-title">U</span>
            <a href="https://jjnztxsb.lanzov.com/b00g2od5cj">Unsleep/失眠/睡不着</a>
            <a href="https://pan.baidu.com/s/1krl4JN3zI9lmAe0AUycghg?pwd=bleh">Under the Leg1-5完结</a>
        </div>
        <div>
            <span class="a-title">V</span>
            <a href="https://flowus.cn/seacomic2/share/03a9f944-6772-4524-a518-09e09935ba17?code=KEKW5D&embed=true">V博士和三个恋人1-20第一季完结</a>
        </div>
                         <!-- W 标签模块 -->
        <div>
            <span class="a-title">W</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b00g3olbda">亡种</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee780d">无根树</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ugoaf">我的S主</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2tiacj">无痕之夜</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2bflvi">未曾有</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3dxr4d">物种进化</a>
            <a href="https://jjnztxsb.lanzov.com/b04eff32j">wish you</a>
            <a href="https://jjnztxsb.lanzov.com/b04efezdg">完美搭档</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5oeb">我的秀赫</a>
            <a href="https://jjnztxsb.lanzov.com/b04efewvg">温柔森林</a>
            <a href="https://jjnztxsb.lanzov.com/b04efeytg">玩具工坊/朝鲜玩具工坊</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3bspza">我最后的冬日</a>
            <a href="https://jjnztxsb.lanzov.com/b00g322sha">妄想的边界</a>
            <a href="https://flowus.cn/seacomic2/share/f8e0f5f3-904e-4453-8ee0-30835330e7ea?code=KEKW5D&embed=true">我那太可爱的家伙 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3fuw8b">我的鼠鼠是S级觉醒者</a>

            <a href="https://jjnztxsb.lanzov.com/b04edyfpg">wetsand湿沙</a>
            <a href="https://jjnztxsb.lanzov.com/b04efezad">玩家的生存法则</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2doucj">我一生中最大的幸运</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ekc2d">未完成的关系</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ekbrc">我被大佬圈养后</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ek9ng">Wolf in the house</a>
            <a href="https://jjnztxsb.lanzov.com/b00g39quqh">我不是奇怪的人 台无光</a>
        </div>
        
        <h4>完结</h4>
        <div>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbz9a">乌之恋</a>
            <a href="https://jjnztxsb.lanzov.com/b04efer2h">物种起源</a>
            <a href="https://pan.baidu.com/s/1jv2iii-t14TeVykrkh-JUA?pwd=bleh">五号公寓</a>
            <a href="https://jjnztxsb.lanzov.com/b00g295xif">无法行走的腿</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2pzv8j">我的半吊子哨兵</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5ilc">无动作minimotion</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2p33ub">乌木城堡</a>
            <a href="https://jjnztxsb.lanzov.com/b00g36cj9c ">无名花1-16完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5oyb">危险便利店</a>

            <a href="https://pan.baidu.com/s/11l16a6q9uCa9g-cDNW01mw?pwd=bleh">误会和误会和误会1-21完结</a>
            <a href="https://pan.baidu.com/s/1baEqgvj-p_-e6Dp_YpVg1Q?pwd=bleh ">我的x爸爸 +外传完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efevih">无线电风暴-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04eff4jc">Kissmelier吻我骗子</a>
            <a href="https://flowus.cn/seacomic2/share/d1ce109e-d5d0-41a1-abe5-457826335a06?code=KEKW5D&embed=true">网路直播主：邦尼 台无光</a>
            <a href="https://pan.baidu.com/s/167rV5fHlzlFLSIwoXrnP5A?pwd=bleh">无窗之屋1-5完结+外传</a>
            <a href="https://jjnztxsb.lanzov.com/b04efewfa">无名/name less第一季完</a>
            <a href="https://jjnztxsb.lanzov.com/b04eff5nc">无缘由／没理由／背叛的理由</a>
        </div>
        
                         <!-- X 标签模块 -->
        <div>
            <span class="a-title">X</span>
            <h4>连载中</h4>
            
            <a href="https://jjnztxsb.lanzov.com/b00g26muib">校园陷阱</a>
            
            <a href="https://jjnztxsb.lanzov.com/b04effloj">行得通吗</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3dsfuh">向导的宝贝</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgswd">幸福的错觉</a>
            <a href="https://jjnztxsb.lanzov.com/b00g30g1ra">薛西弗斯的猎犬 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5ple">咸湿的欲望</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2h76id">虚幻的烙印</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2axqyd">寻觅你的波涛</a>
            <a href="https://jjnztxsb.lanzov.com/b00g34ey4h">新婚夫夫特别录取</a>
            <a href="https://jjnztxsb.lanzov.com/b04efflch">修车危情shutline</a>
            <a href="https://jjnztxsb.lanzov.com/b04efh1yb">现开始社内恋爱</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rlvhg">性感练习生</a>
        </div>
        
        <h4>完结</h4>
        <div>
            <a href="https://jjnztxsb.lanzov.com/b04efhbbi">邂逅</a>
            <a href="https://jjnztxsb.lanzov.com/b00g27pkeb">驯服</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5f9c">相克</a>
            <a href="https://jjnztxsb.lanzov.com/b04efh2be">夏季-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efgzgb">香蕉丑闻</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5pmf">限定时光</a>
            <a href="https://pan.baidu.com/s/1rRGUDngZyqY8T53m-ufQsw?pwd=bleh">现实爱人完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04eobdpc">吸血鬼向导</a>
            <a href="https://pan.baidu.com/s/1A5_rGUk3ul25gJqgxpOf9A?pwd=bleh">信息素与战争1-45完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04effmij">信息素恐惧症</a>
            <a href="https://jjnztxsb.lanzov.com/b04efgz6b">响弦文字-完结</a>
            <a href="https://flowus.cn/seacomic2/share/7b7047ae-e983-45be-900f-c559b5d9ca0b?code=KEKW5D&embed=true">心诚祈愿/祈祷</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2787zc">夏日天空的雷雨</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2b9zte">驯服主人、驯服少爷</a>
            <a href="https://jjnztxsb.lanzov.com/b04ef2iba">相信我的直觉外传5完结</a>
        </div>
        
                     <!-- Y 标签模块 -->
        <div>
            <span class="a-title">Y</span>
            <h4>连载中</h4>
            <a href="https://jjnztxsb.lanzov.com/b04effc9a">月影</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbzcd">月光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g24yzfi">阴影区</a>
            <a href="https://jjnztxsb.lanzov.com/b00g39hkzc">阴森森宝贝</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3iq58d">耀眼的呼吸</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2jiymd">伊甸园 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g36cm2d">云龙风虎 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2vawve">咬后甜蜜解锁</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2waupc">忧郁的错觉 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2szcch">炎之束缚</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2p5mmj">拥神之法 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rtb0h">易地思之</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kbzbc">勇士出击</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2lsvte">异物之家 台无光</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2i4wyd">悠元不变</a>
            <a href="https://jjnztxsb.lanzov.com/b00g24rune">越线关系</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38a8re">忧郁的骗子</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5q9i">引导障碍/疏导障碍</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2doucj">一生中最大的幸运</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3jkdbe">杨日宇和我/杨一宇和我</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2v6xvg">应念而至 台无光【第一季完结】</a>
            <a href="https://jjnztxsb.lanzov.com/b00g34pk8d">妖精的山枝梦/山枝鬼怪之梦</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rpmla">1995青春报告</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2nfsyf">little bit psycho/有一点神经质</a>
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://caiyun.139.com/w/i/2prALuhRb7Vjr">1to10</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3caz1e">一饮而尽 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2cgt1i">云端之恋</a>
            <a href="https://jjnztxsb.lanzov.com/b04efdxbg">野画集</a>
            <a href="https://jjnztxsb.lanzov.com/b04edvb4d">语义错误</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee5i9a">要结婚的男人</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2u458h">厌恶至上</a>
            <a href="https://jjnztxsb.lanzov.com/b00g32cuvg">一步地狱完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2ltykj">有毒的的纽带</a>
            <a href="https://jjnztxsb.lanzov.com/b04efiopg">月下狼嚎-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efffsh">眼罩游戏BlindPlay</a>
            <a href="https://jjnztxsb.lanzov.com/b00g29xdrc">异乡人 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2rbbji">也和我交往吧</a>
            <a href="https://jjnztxsb.lanzov.com/b04effeqj">野兽都该S-完结</a>
            <a href="https://pan.baidu.com/s/12YTNvd-pwjorMHvaubdG7A?pwd=bleh ">油脂商人的菊花滑溜溜-完结</a>
            <a href="https://pan.baidu.com/s/1H2tsbebgyF9_BxMLBiB41Q?pwd=bleh">要跟我一起举铁吗？正篇38+外传7完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efh4pa">裕书先生，那个不能吃哦！-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04effcof">鱼生店老板的初恋是条人鱼-完结</a>
        </div>
        
                             <!-- Z 标签模块 -->
        <div>
            <span class="a-title">Z</span>
            <h4>连载中</h4>

            <a href="https://jjnztxsb.lanzov.com/b00g3i0ivi">Zero side </a>
            <a href="https://flowus.cn/seacomic2/share/c6446863-520e-4004-b451-22089c00716f?code=KEKW5D&embed=true">子弹时间</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3fl17a">追星之恋</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3adash">桎梏之渊</a>
            <a href="https://jjnztxsb.lanzov.com/b04efikkh">主人的私情</a>
            <a href="https://jjnztxsb.lanzov.com/b04efiktg">周一的救星</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2b9zvg">坠入深处</a>
            <a href="https://jjnztxsb.lanzov.com/b00g26muoh">主恩/珠恩</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2kby4j">致不爱你的我</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3gzs5a">宗家破坏者</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2y05la">作战代号马里奥</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3cu78d">坠羊/坠落之羊</a>
            <a href="https://jjnztxsb.lanzov.com/b00g38zu6f">珍珠少年：发火</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3lcgsd">珍珠少年：发火【无光版】</a>
            <a href="https://jjnztxsb.lanzov.com/b00g34azte">骤然袭来/闯入</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3exlrg">尊重喜好/尊重取向</a>
            <a href="https://jjnztxsb.lanzov.com/b00g3e3k4h">主的和平/和平主义</a>
            <a href="https://jjnztxsb.lanzov.com/b04efflwh">周六的主人/星期六的主人</a>
            <a href="https://jjnztxsb.lanzov.com/b04efihaj">自以为是/自我为中心的思考方式</a>
            
        </div>
        
        <h4>完结</h4>
        <div>    
            <a href="https://flowus.cn/seacomic2/share/f0dbe5a9-bda8-4b59-8dc3-b352aaf1f8d5?code=KEKW5D&embed=true">追捕</a>
            <a href="https://jjnztxsb.lanzov.com/b04ed54ta">珍珠少年</a>
            <a href="https://jjnztxsb.lanzov.com/b04efil7a">这样，还喜欢吗</a>
            <a href="https://jjnztxsb.lanzov.com/b04ee706b">制服太粗糙了</a>
            <a href="https://jjnztxsb.lanzov.com/b04efil1e">周日的安慰</a>
            <a href="https://jjnztxsb.lanzov.com/b04eficpe">掌心绽放的花</a>
            <a href="https://jjnztxsb.lanzov.com/b04efid1g">最普通的恋爱</a>
            <a href="https://jjnztxsb.lanzov.com/b00g295x4b">糟糕的罗曼史</a>
            <a href="https://jjnztxsb.lanzov.com/b04efilyh">再度/重生-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b00g2fp6kb">最深情的告白 完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efippc">再次，与你相遇-完结</a>
            <a href="https://jjnztxsb.lanzov.com/b04efitcd">症候群／综合症-完结</a>
            <a href="https://flowus.cn/seacomic2/share/58147b83-ed4e-402d-bb58-af4ffbc73462?code=KEKW5D&embed=true">这里房租怎么这么便宜 台无光</a>
            <a href="https://yun.139.com/shareweb/#/w/i/2qidYwbdtrpyj">专属恋爱禁止区域/beta的恋爱禁止区域 正篇44外传4完结</a>
           
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
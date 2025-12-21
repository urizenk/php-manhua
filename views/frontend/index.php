<?php
/**
 * F1-主界面模块
 * 双列卡片展示 + 访问码拦截
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$pageTitle = '海の小窝';

// 从数据库读取配置
$siteConfig = $db->query("SELECT config_key, config_value FROM site_config");
$configSettings = [];
foreach ($siteConfig as $row) {
    $configSettings[$row['config_key']] = $row['config_value'];
}

$siteName = $configSettings['site_name'] ?? '海の小窝';
$siteDesc = $configSettings['site_desc'] ?? '无偿分享 · 禁止盗卖 · 更多精彩';
$weiboUrl = $configSettings['weibo_url'] ?? '#';
$weiboText = $configSettings['weibo_text'] ?? '微博@资源小站';
$homepageRedirectUrl = $configSettings['homepage_redirect_url'] ?? '';
$accessCodeUrl = $configSettings['access_code_url'] ?? '';
$accessCodeTutorial = $configSettings['access_code_tutorial'] ?? '关注主页即可获取每日访问码';

// 使用首页跳转URL，如果没有设置则使用微博URL
$jumpUrl = $homepageRedirectUrl ?: $weiboUrl;

// 模块类型列表
$types = $db ? $db->query('SELECT * FROM manga_types ORDER BY sort_order, id') : [];

// 当前访问码是否已通过验证
$isAccessVerified = $session ? $session->isAccessVerified() : false;

$customCss = '
<style>
    * {
        box-sizing: border-box;
    }
    body {
        font-family: "Poppins", "Microsoft YaHei", Arial, sans-serif;
        background-color: #FFF8DC;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        min-height: 100dvh; /* 动态视口高度，适配移动端 */
    }
    .container {
        text-align: center;
        width: 92%;
        max-width: 500px;
        background-color: white;
        padding: clamp(20px, 5vw, 30px) clamp(15px, 4vw, 25px);
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 15px auto;
    }
    .page-title {
        font-size: clamp(1.5rem, 5vw, 1.8rem);
        color: #333;
        margin-bottom: 15px;
        font-weight: bold;
        word-break: keep-all;
    }
    .title-notice {
        background-color: #FFF8DC;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .title-notice p {
        margin: 5px 0;
        font-size: clamp(0.85rem, 3vw, 0.95rem);
        font-weight: 500;
        color: #444;
    }
    .title-notice a {
        color: #FF6B35;
        font-weight: bold;
        text-decoration: none;
        word-break: break-all;
    }
    .title-notice a:hover {
        text-decoration: underline;
    }
    .category-nav {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: clamp(8px, 2vw, 12px);
        margin: 15px 0;
    }
    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: clamp(12px, 3vw, 18px) clamp(8px, 2vw, 12px);
        background: white;
        border-radius: 12px;
        text-decoration: none;
        color: #333;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 2px solid #f0f0f0;
        cursor: pointer;
        min-height: 90px;
    }
    .category-item:hover, .category-item:active {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(255,107,53,0.2);
        border-color: #FFA500;
    }
    .category-icon {
        width: clamp(38px, 10vw, 45px);
        height: clamp(38px, 10vw, 45px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 12px;
        margin-bottom: 8px;
        flex-shrink: 0;
    }
    .category-icon i {
        font-size: clamp(1.1rem, 4vw, 1.3rem);
        color: white;
    }
    .category-item span {
        font-weight: 600;
        font-size: clamp(0.8rem, 3vw, 0.95rem);
        color: #333;
        line-height: 1.3;
        word-break: keep-all;
    }
    .footer-notice {
        margin-top: 25px;
        font-size: clamp(0.6rem, 2vw, 0.7rem);
        color: #999;
        line-height: 1.6;
    }
    .footer-notice p {
        margin: 3px 0;
    }
    
    /* 访问码弹窗 */
    .access-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 15px;
    }
    .access-modal.show {
        display: flex;
    }
    .access-modal-content {
        background: white;
        border-radius: 15px;
        width: 100%;
        max-width: 350px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        max-height: 90vh;
        overflow-y: auto;
    }
    .access-modal-header {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        position: sticky;
        top: 0;
    }
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
        padding: 5px;
    }
    .access-modal-body {
        padding: 25px 20px;
    }
    .access-code-input {
        width: 100%;
        padding: 14px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1.1rem;
        text-align: center;
        margin-bottom: 15px;
        box-sizing: border-box;
        -webkit-appearance: none;
    }
    .access-code-input:focus {
        outline: none;
        border-color: #FF6B35;
    }
    .btn-access-submit {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .btn-access-submit:hover, .btn-access-submit:active {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255,107,53,0.3);
    }
    .access-tips {
        text-align: center;
        margin-top: 15px;
        color: #666;
        font-size: 0.9rem;
    }
    .access-tips p {
        margin: 8px 0;
    }
    .btn-get-code {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 20px;
        background: #FFF3E0;
        color: #E65100;
        border: 2px solid #FFB74D;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    .btn-get-code:hover, .btn-get-code:active {
        background: #FFB74D;
        color: white;
    }
    
    /* 响应式调整 */
    @media screen and (max-width: 360px) {
        .container {
            width: 95%;
            padding: 15px 12px;
        }
        .category-nav {
            gap: 8px;
        }
        .category-item {
            padding: 10px 8px;
            min-height: 80px;
        }
    }
    
    /* 针对特殊屏幕高度（如iPhone SE） */
    @media screen and (max-height: 600px) {
        body {
            align-items: flex-start;
            padding-top: 10px;
        }
        .container {
            margin: 10px auto;
        }
    }
    
    /* 安全区域适配（iPhone X等刘海屏） */
    @supports (padding: max(0px)) {
        .container {
            padding-bottom: max(20px, env(safe-area-inset-bottom));
        }
        .access-modal {
            padding-bottom: env(safe-area-inset-bottom);
        }
    }
</style>
';

$customJs = '
<script>
(function() {
    var targetUrl = "";
    var isExternal = false;
    var isVerified = ' . ($isAccessVerified ? 'true' : 'false') . ';
    var accessModal = document.getElementById("accessModal");
    var accessInput = document.getElementById("accessCode");
    var verifyBtn = document.getElementById("verifyBtn");
    var closeBtn = document.getElementById("modalClose");

    if (!accessModal || !accessInput || !verifyBtn) {
        return;
    }

    function openModal() {
        accessModal.classList.add("show");
        accessInput.value = "";
        document.body.style.overflow = "hidden";
        setTimeout(function() {
            accessInput.focus();
        }, 100);
    }

    function closeModal() {
        accessModal.classList.remove("show");
        document.body.style.overflow = "";
    }

    function postVerify(code) {
        var payload = new URLSearchParams({ code: code }).toString();

        if (window.fetch) {
            return fetch("/verify-code", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                },
                body: payload
            }).then(function(resp) { return resp.json(); });
        }

        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/verify-code");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
            xhr.onload = function() {
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch (err) {
                    reject(err);
                }
            };
            xhr.onerror = reject;
            xhr.send(payload);
        });
    }

    document.querySelectorAll(".category-item").forEach(function(card) {
        card.addEventListener("click", function(e) {
            e.preventDefault();
            targetUrl = card.getAttribute("data-url") || "";
            isExternal = card.getAttribute("data-external") === "1";
            
            if (!targetUrl) {
                return;
            }

            // 外部链接直接跳转，不需要验证访问码
            if (isExternal) {
                window.open(targetUrl, "_blank");
                return;
            }

            if (isVerified) {
                window.location.href = targetUrl;
                return;
            }

            openModal();
        });
    });

    verifyBtn.addEventListener("click", function() {
        var code = accessInput.value.trim();
        if (!code) {
            alert("请输入访问码");
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.textContent = "验证中...";

        postVerify(code)
            .then(function(data) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = "提交";

                if (data && data.success) {
                    isVerified = true;
                    closeModal();
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                } else {
                    alert(data.message || "验证失败，请重试");
                }
            })
            .catch(function() {
                verifyBtn.disabled = false;
                verifyBtn.textContent = "提交";
                alert("网络错误，请重试");
            });
    });

    accessInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            verifyBtn.click();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", closeModal);
    }

    accessModal.addEventListener("click", function(e) {
        if (e.target === accessModal) {
            closeModal();
        }
    });
    
    // 防止iOS下的弹出键盘导致页面滚动问题
    accessInput.addEventListener("blur", function() {
        window.scrollTo(0, 0);
    });
})();
</script>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="container">
    <h1 class="page-title">欢迎来到<?php echo htmlspecialchars($siteName); ?></h1>
    
    <!-- 标题下的提示语 -->
    <div class="title-notice">
        <p><?php echo htmlspecialchars($siteDesc); ?></p>
        <p><a href="<?php echo htmlspecialchars($jumpUrl); ?>" target="_blank"><?php echo htmlspecialchars($weiboText); ?></a></p>
    </div>

    <!-- 漫画分类导航 -->
    <div class="category-nav">
        <?php if (empty($types)): ?>
            <p style="grid-column: span 2; color: #999;">尚未配置模块</p>
        <?php else: ?>
            <?php foreach ($types as $type): ?>
                <?php
                    $code = $type['type_code'];
                    $icon = $type['icon'] ?? 'book';
                    $externalUrl = $type['external_url'] ?? '';
                    // 如果有外部链接，使用外部链接；否则使用内部模块链接
                    $url = $externalUrl ?: module_url($code);
                    $isExternal = !empty($externalUrl) ? '1' : '0';
                ?>
                <div class="category-item" data-url="<?php echo htmlspecialchars($url); ?>" data-external="<?php echo $isExternal; ?>">
                    <div class="category-icon">
                        <i class="bi bi-<?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <span><?php echo htmlspecialchars($type['type_name']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 页面底部提示语 -->
    <div class="footer-notice">
        <p>本网站网址数据来源于互联网搜索</p>
        <p>和热心网友投稿，喜欢请支持作者</p>
        <p>Copyright ©2024 本地保存请勿超过24小时 特此声明</p>
    </div>
</div>

<!-- 访问码验证弹窗 -->
<div class="access-modal" id="accessModal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <span>请输入访问码</span>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="access-modal-body">
            <input type="text"
                   class="access-code-input"
                   id="accessCode"
                   placeholder="输入访问码"
                   autocomplete="off"
                   inputmode="text">
            <button type="button" class="btn-access-submit" id="verifyBtn">提交</button>
            <div class="access-tips">
                <p><?php echo htmlspecialchars($accessCodeTutorial); ?></p>
                <?php if ($accessCodeUrl): ?>
                    <a href="<?php echo htmlspecialchars($accessCodeUrl); ?>" target="_blank" class="btn-get-code">
                        <i class="bi bi-box-arrow-up-right"></i> 点击获取访问码
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

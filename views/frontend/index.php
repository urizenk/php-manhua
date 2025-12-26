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
$accessCodeUrls = json_decode($configSettings['access_code_urls'] ?? '[]', true) ?: [];
$accessCodeTutorial = $configSettings['access_code_tutorial'] ?? '';

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
        font-family: "Poppins", "Microsoft YaHei", Arial, sans-serif !important;
        background-color: #FFF8DC !important;
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
        min-height: 100dvh;
    }
    .home-container {
        text-align: center;
        width: 100%;
        max-width: 100%;
        background-color: white;
        padding: 25px 20px 30px;
        margin: 0;
        min-height: 100vh;
        min-height: 100dvh;
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
        width: auto;
        min-width: 120px;
        padding: 10px 25px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        -webkit-tap-highlight-color: transparent;
        margin: 0 auto;
        display: block;
    }
    .btn-access-submit:hover, .btn-access-submit:active {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255,107,53,0.3);
    }
    .access-tips {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 0.9rem;
    }
    .access-tips p {
        margin: 8px 0;
    }
    .tutorial-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #FF6B35;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.9rem;
        margin-bottom: 12px;
    }
    .tutorial-link:hover {
        text-decoration: underline;
        color: #E65100;
    }
    .get-code-title {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #FFF3E0;
        padding: 8px 16px;
        border-radius: 20px;
        border: 1px dashed #FFB74D;
        color: #E65100;
        font-size: 0.85rem;
        font-weight: bold;
        margin-bottom: 12px;
    }
    .get-code-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }
    .btn-get-code {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px 20px;
        background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .btn-get-code:nth-child(2) {
        background: linear-gradient(135deg, #673AB7 0%, #512DA8 100%);
    }
    .btn-get-code:nth-child(3) {
        background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);
    }
    .btn-get-code:hover, .btn-get-code:active {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        color: white;
    }
    
    /* 响应式调整 */
    @media screen and (max-width: 360px) {
        .home-container {
            padding: 20px 15px 25px;
        }
        .category-nav {
            gap: 8px;
        }
        .category-item {
            padding: 10px 8px;
            min-height: 80px;
        }
    }
    
    /* 安全区域适配（iPhone X等刘海屏） */
    @supports (padding: max(0px)) {
        .home-container {
            padding-bottom: max(30px, env(safe-area-inset-bottom));
            padding-left: max(20px, env(safe-area-inset-left));
            padding-right: max(20px, env(safe-area-inset-right));
        }
        .access-modal {
            padding-bottom: env(safe-area-inset-bottom);
        }
    }
    
    /* 图片弹窗 */
    .popup-image-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85);
        z-index: 10000;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .popup-image-modal.show {
        display: flex;
    }
    .popup-image-content {
        max-width: 90%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    }
    .popup-image-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .popup-image-close:hover {
        background: rgba(255,255,255,0.3);
    }
    .popup-image-hint {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        font-size: 0.9rem;
        background: rgba(0,0,0,0.5);
        padding: 8px 20px;
        border-radius: 20px;
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
    var popupImageModal = document.getElementById("popupImageModal");
    var popupImage = document.getElementById("popupImageContent");
    var popupImageCloseBtn = document.getElementById("popupImageClose");

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
    
    function openPopupImage(imageUrl, navigateUrl) {
        if (popupImageModal && popupImage) {
            popupImage.src = imageUrl;
            popupImageModal.classList.add("show");
            popupImageModal.setAttribute("data-navigate-url", navigateUrl);
            document.body.style.overflow = "hidden";
        }
    }
    
    function closePopupImage() {
        if (popupImageModal) {
            popupImageModal.classList.remove("show");
            document.body.style.overflow = "";
        }
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
            var popupImageUrl = card.getAttribute("data-popup-image") || "";
            
            if (!targetUrl) {
                return;
            }

            // 外部链接直接跳转，不需要验证访问码
            if (isExternal) {
                window.open(targetUrl, "_blank");
                return;
            }

            // 如果有弹窗图片，先显示图片
            if (popupImageUrl && isVerified) {
                openPopupImage(popupImageUrl, targetUrl);
                return;
            }

            if (isVerified) {
                window.location.href = targetUrl;
                return;
            }

            openModal();
        });
    });
    
    // 弹窗图片点击关闭并跳转
    if (popupImageModal) {
        popupImageModal.addEventListener("click", function(e) {
            if (e.target === popupImageModal || e.target === popupImage) {
                var navigateUrl = popupImageModal.getAttribute("data-navigate-url");
                closePopupImage();
                if (navigateUrl) {
                    window.location.href = navigateUrl;
                }
            }
        });
    }
    
    if (popupImageCloseBtn) {
        popupImageCloseBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            var navigateUrl = popupImageModal.getAttribute("data-navigate-url");
            closePopupImage();
            if (navigateUrl) {
                window.location.href = navigateUrl;
            }
        });
    }

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

<div class="home-container">
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
                    $popupImage = $type['popup_image'] ?? '';
                    // 如果有外部链接，使用外部链接；否则使用内部模块链接
                    $url = $externalUrl ?: module_url($code);
                    $isExternal = !empty($externalUrl) ? '1' : '0';
                ?>
                <div class="category-item" 
                     data-url="<?php echo htmlspecialchars($url); ?>" 
                     data-external="<?php echo $isExternal; ?>"
                     data-popup-image="<?php echo htmlspecialchars($popupImage); ?>">
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

<!-- 图片弹窗 -->
<div class="popup-image-modal" id="popupImageModal">
    <button type="button" class="popup-image-close" id="popupImageClose">&times;</button>
    <img src="" alt="模块图片" class="popup-image-content" id="popupImageContent">
    <div class="popup-image-hint">点击任意位置继续</div>
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
                   placeholder="输入密码，不会就看下方取码教程"
                   autocomplete="off"
                   inputmode="text">
            <button type="button" class="btn-access-submit" id="verifyBtn">提交</button>
            
            <?php if ($accessCodeTutorial || !empty($accessCodeUrls)): ?>
            <div class="access-tips">
                <?php if ($accessCodeTutorial): ?>
                    <?php 
                    // 检查教程是否为链接
                    $tutorialIsUrl = preg_match('/^https?:\/\//i', trim($accessCodeTutorial));
                    ?>
                    <?php if ($tutorialIsUrl): ?>
                        <a href="<?php echo htmlspecialchars(trim($accessCodeTutorial)); ?>" target="_blank" class="tutorial-link">
                            🎉取码教程
                        </a>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars(trim($accessCodeTutorial)); ?>" target="_blank" class="tutorial-link">
                            🎉取码教程
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (!empty($accessCodeUrls)): ?>
                    <div class="get-code-title">获取每日访问码👇</div>
                    <div class="get-code-links">
                        <?php foreach ($accessCodeUrls as $urlItem): ?>
                            <?php if (!empty($urlItem['url'])): ?>
                                <a href="<?php echo htmlspecialchars($urlItem['url']); ?>" target="_blank" class="btn-get-code">
                                    <?php echo htmlspecialchars($urlItem['name'] ?: '获取访问码'); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

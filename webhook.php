<?php
/**
 * Gitee Webhook 自动化部署接收端点
 * 
 * 配置说明：
 * 1. 将此文件放在服务器的 public 目录下（例如：/www/wwwroot/php-manhua/public/webhook.php）
 * 2. 在 Gitee 仓库设置中配置 Webhook URL：http://your-domain.com/webhook.php
 * 3. 设置 Webhook 密钥（Secret Token）
 * 4. 选择触发事件：Push
 */

// ==========================================
// 配置区域
// ==========================================

// Webhook 密钥（必须与 Gitee 中配置的一致）
define('WEBHOOK_SECRET', 'your_webhook_secret_here_change_me');

// 项目根目录（绝对路径）
define('PROJECT_ROOT', '/var/www/php-manhua');

// Git 分支
define('GIT_BRANCH', 'main');

// 部署日志文件
define('DEPLOY_LOG', PROJECT_ROOT . '/storage/logs/deploy.log');

// 部署脚本路径
define('DEPLOY_SCRIPT', PROJECT_ROOT . '/scripts/auto-deploy.sh');

// 是否启用详细日志
define('VERBOSE_LOG', true);

// ==========================================
// 日志函数
// ==========================================

function writeLog($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
    
    // 确保日志目录存在
    $logDir = dirname(DEPLOY_LOG);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // 写入日志文件
    file_put_contents(DEPLOY_LOG, $logMessage, FILE_APPEND);
    
    // 如果启用详细日志，也输出到响应
    if (VERBOSE_LOG) {
        echo $logMessage;
    }
}

// ==========================================
// 验证 Webhook 签名
// ==========================================

function verifySignature() {
    // 获取 Gitee 发送的签名
    $signature = $_SERVER['HTTP_X_GITEE_TOKEN'] ?? '';
    
    if (empty($signature)) {
        writeLog('未收到 Gitee 签名头', 'ERROR');
        return false;
    }
    
    // 验证签名
    if ($signature !== WEBHOOK_SECRET) {
        writeLog('Webhook 签名验证失败', 'ERROR');
        return false;
    }
    
    writeLog('Webhook 签名验证成功', 'SUCCESS');
    return true;
}

// ==========================================
// 解析 Webhook 数据
// ==========================================

function parseWebhookData() {
    $rawData = file_get_contents('php://input');
    
    if (empty($rawData)) {
        writeLog('未收到 Webhook 数据', 'ERROR');
        return null;
    }
    
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog('Webhook 数据解析失败: ' . json_last_error_msg(), 'ERROR');
        return null;
    }
    
    return $data;
}

// ==========================================
// 执行部署
// ==========================================

function executeDeploy($data) {
    // 检查是否是目标分支
    $ref = $data['ref'] ?? '';
    $branch = str_replace('refs/heads/', '', $ref);
    
    if ($branch !== GIT_BRANCH) {
        writeLog("忽略非目标分支的推送: {$branch}", 'INFO');
        return [
            'success' => false,
            'message' => "只部署 {GIT_BRANCH} 分支，当前分支: {$branch}"
        ];
    }
    
    // 获取提交信息
    $commits = $data['commits'] ?? [];
    $commitCount = count($commits);
    $pusher = $data['pusher']['name'] ?? 'Unknown';
    
    writeLog("收到来自 {$pusher} 的推送，包含 {$commitCount} 个提交", 'INFO');
    
    // 记录提交信息
    foreach ($commits as $commit) {
        $message = $commit['message'] ?? '';
        $author = $commit['author']['name'] ?? 'Unknown';
        writeLog("  - {$author}: {$message}", 'INFO');
    }
    
    // 检查部署脚本是否存在
    if (!file_exists(DEPLOY_SCRIPT)) {
        writeLog("部署脚本不存在: " . DEPLOY_SCRIPT, 'ERROR');
        return [
            'success' => false,
            'message' => '部署脚本不存在'
        ];
    }
    
    // 执行部署脚本
    writeLog('开始执行部署脚本...', 'INFO');
    
    $command = 'bash ' . escapeshellarg(DEPLOY_SCRIPT) . ' 2>&1';
    $output = [];
    $returnCode = 0;
    
    exec($command, $output, $returnCode);
    
    // 记录部署输出
    foreach ($output as $line) {
        writeLog($line, 'DEPLOY');
    }
    
    if ($returnCode === 0) {
        writeLog('部署成功完成', 'SUCCESS');
        return [
            'success' => true,
            'message' => '部署成功',
            'output' => $output
        ];
    } else {
        writeLog("部署失败，退出码: {$returnCode}", 'ERROR');
        return [
            'success' => false,
            'message' => '部署失败',
            'return_code' => $returnCode,
            'output' => $output
        ];
    }
}

// ==========================================
// 主流程
// ==========================================

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 记录请求开始
writeLog('========================================', 'INFO');
writeLog('收到 Webhook 请求', 'INFO');
writeLog('请求方法: ' . $_SERVER['REQUEST_METHOD'], 'INFO');
writeLog('请求来源: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'), 'INFO');

// 只接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog('拒绝非 POST 请求', 'ERROR');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// 验证签名
if (!verifySignature()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid signature'
    ]);
    exit;
}

// 解析 Webhook 数据
$data = parseWebhookData();
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid webhook data'
    ]);
    exit;
}

// 执行部署
$result = executeDeploy($data);

// 返回结果
http_response_code($result['success'] ? 200 : 500);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

writeLog('Webhook 处理完成', 'INFO');
writeLog('========================================', 'INFO');

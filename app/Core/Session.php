<?php
/**
 * C3-会话管理模块
 * 提供访问码验证和Session管理
 */

namespace App\Core;

class Session
{
    private static $started = false;
    private $db = null;
    private $sessionConfig = [];

    private const ACCESS_COOKIE_VERSION = 1;

    public function __construct($config = [])
    {
        $this->start($config);
    }

    /**
     * 启动Session
     */
    public function start($config = [])
    {
        $this->sessionConfig = is_array($config) ? $config : [];

        if (self::$started) {
            return;
        }

        if (!empty($this->sessionConfig['name'])) {
            session_name($this->sessionConfig['name']);
        }

        $lifetime = !empty($this->sessionConfig['lifetime']) ? (int)$this->sessionConfig['lifetime'] : 0;
        $effectiveLifetime = $lifetime > 0 ? max($lifetime, 43200) : 43200;
        $this->sessionConfig['lifetime'] = $effectiveLifetime;
        ini_set('session.gc_maxlifetime', (string)$effectiveLifetime);
        ini_set('session.cookie_lifetime', (string)$effectiveLifetime);

        $cookieHttpOnly = !empty($this->sessionConfig['cookie_httponly']);
        $cookieSecure = !empty($this->sessionConfig['cookie_secure']);
        $cookieSameSite = !empty($this->sessionConfig['cookie_samesite']) ? (string)$this->sessionConfig['cookie_samesite'] : null;

        if ($cookieHttpOnly) {
            ini_set('session.cookie_httponly', '1');
        }

        if ($cookieSecure) {
            ini_set('session.cookie_secure', '1');
        }

        if ($cookieSameSite) {
            ini_set('session.cookie_samesite', $cookieSameSite);
        }

        if (!headers_sent()) {
            $cookieParams = session_get_cookie_params();
            $params = [
                'lifetime' => $effectiveLifetime,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => $cookieSecure ? true : $cookieParams['secure'],
                'httponly' => $cookieHttpOnly ? true : $cookieParams['httponly'],
            ];
            if ($cookieSameSite) {
                $params['samesite'] = $cookieSameSite;
            }
            session_set_cookie_params($params);
        }
        
        if (!empty($this->sessionConfig['use_strict_mode'])) {
            ini_set('session.use_strict_mode', 1);
        }
        
        if (!empty($this->sessionConfig['sid_length'])) {
            ini_set('session.sid_length', (string)$this->sessionConfig['sid_length']);
        }
        
        if (!empty($this->sessionConfig['sid_bits_per_character'])) {
            ini_set('session.sid_bits_per_character', (string)$this->sessionConfig['sid_bits_per_character']);
        }

        session_start();
        self::$started = true;
    }

    /**
     * 设置Session值
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 获取Session值
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * 检查Session是否存在
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * 删除Session值
     */
    public function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * 清空所有Session
     */
    public function destroy()
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * 验证访问码
     * @param string $inputCode 用户输入的访问码
     * @return bool 是否验证通过
     */
    public function verifyAccessCode($inputCode, $db)
    {
        $this->db = $db;

        // 获取正确的访问码
        $correctCode = $this->getAccessCode();

        // 验证
        $isValid = ($inputCode === $correctCode);

        // 记录访问日志
        $this->logAccess($inputCode, $isValid);

        // 验证通过则设置Session
        if ($isValid) {
            $this->set('access_verified', true);
            $this->set('access_time', time());
            // 记录当前通过验证的访问码，用于后续校验是否已被修改
            $this->set('access_code_value', $correctCode);

            // 写入签名Cookie，避免Session丢失导致重复输入
            $this->setAccessPassCookie($correctCode);
        }

        return $isValid;
    }

    /**
     * 检查访问码是否已验证
     */
    public function isAccessVerified()
    {
        if (!$this->has('access_verified')) {
            return $this->restoreAccessFromCookie();
        }

        // 如果访问码已经被修改，则要求重新验证
        $currentCode = $this->getAccessCode();
        $verifiedCode = $this->get('access_code_value');
        if (empty($verifiedCode) || $verifiedCode !== $currentCode) {
            $this->delete('access_verified');
            $this->delete('access_time');
            $this->delete('access_code_value');
            $this->clearAccessPassCookie();
            return $this->restoreAccessFromCookie();
        }

        // 检查是否超时（可选）
        $accessTime = $this->get('access_time', 0);
        $timeout = (int)($this->sessionConfig['access_timeout'] ?? $this->sessionConfig['lifetime'] ?? 43200);
        if ($timeout <= 0) {
            $timeout = 43200;
        }
        
        if (time() - $accessTime > $timeout) {
            $this->delete('access_verified');
            $this->delete('access_time');
            $this->clearAccessPassCookie();
            return $this->restoreAccessFromCookie();
        }

        return true;
    }

    /**
     * 设置数据库实例
     */
    public function setDatabase($db)
    {
        $this->db = $db;
    }
    
    /**
     * 获取当前访问码
     */
    public function getAccessCode()
    {
        if (!$this->db) {
            return '1024'; // 默认访问码
        }

        $result = $this->db->queryOne(
            "SELECT config_value FROM site_config WHERE config_key = ?",
            ['access_code']
        );

        return $result ? $result['config_value'] : '1024';
    }

    /**
     * 更新访问码
     * @param string $newCode 新访问码
     * @return bool 是否成功
     */
    public function updateAccessCode($newCode)
    {
        if (!$this->db) {
            return false;
        }

        $affected = $this->db->update(
            'site_config',
            ['config_value' => $newCode],
            'config_key = ?',
            ['access_code']
        );

        if ($affected > 0) {
            // 当前用户的访问验证应失效
            $this->delete('access_verified');
            $this->delete('access_time');
            $this->delete('access_code_value');
            $this->clearAccessPassCookie();
            return true;
        }

        return false;
    }

    private function getAccessCookieName()
    {
        $name = $this->sessionConfig['access_cookie_name'] ?? 'MANHUA_ACCESS';
        $name = preg_replace('/[^A-Za-z0-9_\\-]/', '', (string)$name);
        $name = $name ?: 'MANHUA_ACCESS';

        // 如果是 HTTPS，优先使用 __Host- 前缀（更安全：必须 Path=/ 且不能设置 Domain）
        $secure = !empty($this->sessionConfig['cookie_secure']);
        if ($secure && strpos($name, '__Host-') !== 0) {
            $name = '__Host-' . $name;
        }

        return $name;
    }

    private function getAccessCookieSecret()
    {
        // 优先使用环境变量，避免把密钥写进代码仓库
        $env = getenv('MANHUA_ACCESS_COOKIE_SECRET');
        if (is_string($env)) {
            $env = trim($env);
            if ($this->isValidAccessCookieSecret($env)) {
                return $env;
            }
        }

        $secret = (string)($this->sessionConfig['access_cookie_secret'] ?? '');
        $secret = trim($secret);
        return $this->isValidAccessCookieSecret($secret) ? $secret : '';
    }

    private function getAccessCookieTtl()
    {
        $ttl = (int)($this->sessionConfig['access_cookie_ttl'] ?? 43200);
        return $ttl > 0 ? $ttl : 43200;
    }

    private function isValidAccessCookieSecret($secret)
    {
        if (!is_string($secret)) {
            return false;
        }
        $secret = trim($secret);
        if ($secret === '') {
            return false;
        }
        // 防止使用示例占位符
        $upper = strtoupper($secret);
        if (strpos($upper, 'CHANGE_ME') !== false) {
            return false;
        }
        // 至少 32 字符（建议 32+，避免弱密钥）
        return strlen($secret) >= 32;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($data, true);
    }

    private function computeAccessCodeFingerprint($accessCode, $secret)
    {
        return hash_hmac('sha256', (string)$accessCode, (string)$secret);
    }

    private function setAccessPassCookie($accessCode)
    {
        $secret = $this->getAccessCookieSecret();
        if ($secret === '' || headers_sent()) {
            return false;
        }

        $ttl = $this->getAccessCookieTtl();
        $now = time();
        $exp = $now + $ttl;

        $payload = json_encode([
            'v' => self::ACCESS_COOKIE_VERSION,
            'iat' => $now,
            'exp' => $exp,
            'cv' => $this->computeAccessCodeFingerprint($accessCode, $secret),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            return false;
        }

        $payloadB64 = $this->base64UrlEncode($payload);
        $sigRaw = hash_hmac('sha256', $payloadB64, $secret, true);
        $sigB64 = $this->base64UrlEncode($sigRaw);

        $token = $payloadB64 . '.' . $sigB64;

        $sameSite = $this->sessionConfig['cookie_samesite'] ?? 'Lax';
        $secure = !empty($this->sessionConfig['cookie_secure']);

        return setcookie($this->getAccessCookieName(), $token, [
            'expires' => $exp,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite ?: 'Lax',
        ]);
    }

    private function clearAccessPassCookie()
    {
        if (headers_sent()) {
            return false;
        }

        $sameSite = $this->sessionConfig['cookie_samesite'] ?? 'Lax';
        $secure = !empty($this->sessionConfig['cookie_secure']);

        return setcookie($this->getAccessCookieName(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite ?: 'Lax',
        ]);
    }

    private function restoreAccessFromCookie()
    {
        $secret = $this->getAccessCookieSecret();
        if ($secret === '') {
            return false;
        }

        $cookieName = $this->getAccessCookieName();
        $token = $_COOKIE[$cookieName] ?? '';
        if (!is_string($token) || $token === '') {
            return false;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            $this->clearAccessPassCookie();
            return false;
        }

        list($payloadB64, $sigB64) = $parts;
        $sigRaw = $this->base64UrlDecode($sigB64);
        if ($sigRaw === false) {
            $this->clearAccessPassCookie();
            return false;
        }

        $expectedSig = hash_hmac('sha256', $payloadB64, $secret, true);
        if (!hash_equals($expectedSig, $sigRaw)) {
            $this->clearAccessPassCookie();
            return false;
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);
        if ($payloadJson === false) {
            $this->clearAccessPassCookie();
            return false;
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            $this->clearAccessPassCookie();
            return false;
        }

        if (($payload['v'] ?? null) !== self::ACCESS_COOKIE_VERSION) {
            $this->clearAccessPassCookie();
            return false;
        }

        $exp = (int)($payload['exp'] ?? 0);
        $iat = (int)($payload['iat'] ?? 0);
        $now = time();
        if ($exp <= 0 || $iat <= 0 || $exp < $now) {
            $this->clearAccessPassCookie();
            return false;
        }

        // 防止异常 token（例如 iat 在未来 / exp 过长）
        if ($iat > ($now + 300)) {
            $this->clearAccessPassCookie();
            return false;
        }
        $ttl = $this->getAccessCookieTtl();
        if (($exp - $iat) > ($ttl + 60)) {
            $this->clearAccessPassCookie();
            return false;
        }

        $currentCode = $this->getAccessCode();
        $expectedCv = $this->computeAccessCodeFingerprint($currentCode, $secret);
        if (!hash_equals((string)($payload['cv'] ?? ''), $expectedCv)) {
            $this->clearAccessPassCookie();
            return false;
        }

        $this->set('access_verified', true);
        $this->set('access_time', $iat);
        $this->set('access_code_value', $currentCode);

        return true;
    }

    /**
     * 记录访问日志
     */
    private function logAccess($inputCode, $isValid)
    {
        if (!$this->db) {
            return;
        }

        try {
            $this->db->insert('access_logs', [
                'ip_address'  => $this->getClientIp(),
                'access_code' => $inputCode,
                'is_success'  => $isValid ? 1 : 0,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不影响主流程
        }
    }

    /**
     * 获取客户端IP
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * 管理员登录
     */
    public function adminLogin($adminId, $username)
    {
        $this->set('admin_id', $adminId);
        $this->set('admin_username', $username);
        $this->set('admin_login_time', time());
    }

    /**
     * 管理员登出
     */
    public function adminLogout()
    {
        $this->delete('admin_id');
        $this->delete('admin_username');
        $this->delete('admin_login_time');
    }

    /**
     * 检查管理员是否已登录
     */
    public function isAdminLoggedIn()
    {
        return $this->has('admin_id');
    }

    /**
     * 获取当前管理员ID
     */
    public function getAdminId()
    {
        return $this->get('admin_id');
    }
    
    /**
     * 生成CSRF Token
     */
    public function generateCsrfToken()
    {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }
    
    /**
     * 获取CSRF Token
     */
    public function getCsrfToken()
    {
        return $this->generateCsrfToken();
    }
    
    /**
     * 验证CSRF Token
     */
    public function verifyCsrfToken($token)
    {
        $sessionToken = $this->get('csrf_token');
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * 生成CSRF隐藏字段HTML
     */
    public function csrfField()
    {
        $token = $this->getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

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
        }

        return $isValid;
    }

    /**
     * 检查访问码是否已验证
     */
    public function isAccessVerified()
    {
        if (!$this->has('access_verified')) {
            return false;
        }

        // 如果访问码已经被修改，则要求重新验证
        $currentCode = $this->getAccessCode();
        $verifiedCode = $this->get('access_code_value');
        if (empty($verifiedCode) || $verifiedCode !== $currentCode) {
            $this->delete('access_verified');
            $this->delete('access_time');
            $this->delete('access_code_value');
            return false;
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
            return false;
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

        return $affected > 0;
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

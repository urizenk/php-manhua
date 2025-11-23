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

    public function __construct($config = [])
    {
        $this->start($config);
    }

    /**
     * 启动Session
     */
    public function start($config = [])
    {
        if (self::$started) {
            return;
        }

        if (!empty($config['name'])) {
            session_name($config['name']);
        }

        if (!empty($config['lifetime'])) {
            ini_set('session.gc_maxlifetime', $config['lifetime']);
        }

        if (!empty($config['cookie_httponly'])) {
            ini_set('session.cookie_httponly', 1);
        }

        if (!empty($config['cookie_secure'])) {
            ini_set('session.cookie_secure', 1);
        }
        
        // 新增安全配置
        if (!empty($config['cookie_samesite'])) {
            ini_set('session.cookie_samesite', $config['cookie_samesite']);
        }
        
        if (!empty($config['use_strict_mode'])) {
            ini_set('session.use_strict_mode', 1);
        }
        
        if (!empty($config['sid_length'])) {
            ini_set('session.sid_length', $config['sid_length']);
        }
        
        if (!empty($config['sid_bits_per_character'])) {
            ini_set('session.sid_bits_per_character', $config['sid_bits_per_character']);
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

        // 检查是否超时（可选）
        $accessTime = $this->get('access_time', 0);
        $timeout = 7200; // 2小时
        
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
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'input_code' => $inputCode,
                'is_valid' => $isValid ? 1 : 0,
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
}



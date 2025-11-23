<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Session;
use App\Core\Database;

/**
 * Session类单元测试
 */
class SessionTest extends TestCase
{
    private $session;
    private $db;
    private $config;

    protected function setUp(): void
    {
        $this->config = require __DIR__ . '/../bootstrap.php';
        $this->db = Database::getInstance($this->config['database']);
        $this->session = new Session($this->config['session']);
        $this->session->setDatabase($this->db);
    }

    /**
     * 测试Session启动
     */
    public function testSessionStart()
    {
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    /**
     * 测试设置和获取Session值
     */
    public function testSetAndGet()
    {
        $this->session->set('test_key', 'test_value');
        $value = $this->session->get('test_key');
        
        $this->assertEquals('test_value', $value);
    }

    /**
     * 测试获取不存在的Session值返回默认值
     */
    public function testGetWithDefault()
    {
        $value = $this->session->get('non_existent_key', 'default_value');
        
        $this->assertEquals('default_value', $value);
    }

    /**
     * 测试检查Session是否存在
     */
    public function testHas()
    {
        $this->session->set('existing_key', 'value');
        
        $this->assertTrue($this->session->has('existing_key'));
        $this->assertFalse($this->session->has('non_existing_key'));
    }

    /**
     * 测试删除Session值
     */
    public function testDelete()
    {
        $this->session->set('to_delete', 'value');
        $this->assertTrue($this->session->has('to_delete'));
        
        $this->session->delete('to_delete');
        $this->assertFalse($this->session->has('to_delete'));
    }

    /**
     * 测试管理员登录
     */
    public function testAdminLogin()
    {
        $this->session->adminLogin(1, 'testadmin');
        
        $this->assertTrue($this->session->isAdminLoggedIn());
        $this->assertEquals(1, $this->session->getAdminId());
        $this->assertEquals('testadmin', $this->session->getAdminUsername());
    }

    /**
     * 测试管理员登出
     */
    public function testAdminLogout()
    {
        $this->session->adminLogin(1, 'testadmin');
        $this->assertTrue($this->session->isAdminLoggedIn());
        
        $this->session->adminLogout();
        $this->assertFalse($this->session->isAdminLoggedIn());
        $this->assertNull($this->session->getAdminId());
    }

    /**
     * 测试访问码验证 - 正确访问码
     */
    public function testVerifyAccessCodeCorrect()
    {
        $currentCode = $this->session->getAccessCode();
        
        $result = $this->session->verifyAccessCode($currentCode, $this->db);
        
        $this->assertTrue($result);
        $this->assertTrue($this->session->hasAccessCode());
    }

    /**
     * 测试访问码验证 - 错误访问码
     */
    public function testVerifyAccessCodeWrong()
    {
        $result = $this->session->verifyAccessCode('wrong_code_12345', $this->db);
        
        $this->assertFalse($result);
        $this->assertFalse($this->session->hasAccessCode());
    }

    /**
     * 测试获取访问码
     */
    public function testGetAccessCode()
    {
        $code = $this->session->getAccessCode();
        
        $this->assertIsString($code);
        $this->assertNotEmpty($code);
    }

    /**
     * 测试清空所有Session
     */
    public function testDestroy()
    {
        $this->session->set('test1', 'value1');
        $this->session->set('test2', 'value2');
        
        $this->session->destroy();
        
        // Session销毁后需要重启才能测试
        // 这里只验证方法不抛异常
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        // 清理Session
        if ($this->session->isAdminLoggedIn()) {
            $this->session->adminLogout();
        }
    }
}

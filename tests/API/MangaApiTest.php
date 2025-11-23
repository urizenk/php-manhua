<?php

namespace Tests\API;

use PHPUnit\Framework\TestCase;

/**
 * 漫画管理API测试
 */
class MangaApiTest extends TestCase
{
    private $baseUrl;
    private $adminCookie;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost/admin88/api';
        
        // 模拟管理员登录获取Cookie
        $this->adminLogin();
    }

    /**
     * 模拟管理员登录
     */
    private function adminLogin()
    {
        // 这里简化处理，实际测试中需要先登录获取session
        $this->adminCookie = 'PHPSESSID=test_session_id';
    }

    /**
     * 模拟POST请求
     */
    private function post($url, $data = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $this->adminCookie);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    /**
     * 模拟GET请求
     */
    private function get($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $this->adminCookie);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    /**
     * 测试获取标签API
     */
    public function testGetTags()
    {
        $url = $this->baseUrl . '/get-tags.php?type_id=1';
        $response = $this->get($url);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
    }

    /**
     * 测试创建标签API
     */
    public function testCreateTag()
    {
        $url = $this->baseUrl . '/create-tag.php';
        $data = [
            'type_id' => 1,
            'tag_name' => 'API Test Tag ' . time()
        ];
        
        $response = $this->post($url, $data);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('success', $response['body']);
        
        if ($response['body']['success']) {
            $this->assertArrayHasKey('tag_id', $response['body']);
            $this->assertGreaterThan(0, $response['body']['tag_id']);
        }
    }

    /**
     * 测试创建标签 - 缺少参数
     */
    public function testCreateTagMissingParams()
    {
        $url = $this->baseUrl . '/create-tag.php';
        $data = [
            'type_id' => 1
            // 缺少 tag_name
        ];
        
        $response = $this->post($url, $data);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('参数不完整', $response['body']['message']);
    }

    /**
     * 测试删除漫画API
     */
    public function testDeleteManga()
    {
        // 注意：这个测试会实际删除数据，需谨慎
        // 建议在测试环境中运行
        
        $url = $this->baseUrl . '/delete-manga.php';
        $data = [
            'id' => 99999 // 使用不存在的ID
        ];
        
        $response = $this->post($url, $data);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('success', $response['body']);
    }

    /**
     * 测试删除漫画 - 无效ID
     */
    public function testDeleteMangaInvalidId()
    {
        $url = $this->baseUrl . '/delete-manga.php';
        $data = [
            'id' => 0
        ];
        
        $response = $this->post($url, $data);
        
        $this->assertEquals(200, $response['code']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('参数错误', $response['body']['message']);
    }
}

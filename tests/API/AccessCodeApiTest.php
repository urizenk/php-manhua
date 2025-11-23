<?php

namespace Tests\API;

use PHPUnit\Framework\TestCase;

/**
 * 访问码验证API测试
 */
class AccessCodeApiTest extends TestCase
{
    private $baseUrl;
    private $correctCode;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost';
        $this->correctCode = '1024'; // 默认访问码
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
     * 测试正确的访问码
     */
    public function testVerifyCorrectCode()
    {
        $url = $this->baseUrl . '/verify-code';
        $response = $this->post($url, ['code' => $this->correctCode]);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('success', $response['body']);
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('验证成功', $response['body']['message']);
    }

    /**
     * 测试错误的访问码
     */
    public function testVerifyWrongCode()
    {
        $url = $this->baseUrl . '/verify-code';
        $response = $this->post($url, ['code' => 'wrong_code']);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('success', $response['body']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('访问码错误', $response['body']['message']);
    }

    /**
     * 测试空访问码
     */
    public function testVerifyEmptyCode()
    {
        $url = $this->baseUrl . '/verify-code';
        $response = $this->post($url, ['code' => '']);
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertFalse($response['body']['success']);
    }

    /**
     * 测试SQL注入攻击
     */
    public function testSqlInjectionAttempt()
    {
        $url = $this->baseUrl . '/verify-code';
        $maliciousCode = "' OR '1'='1";
        $response = $this->post($url, ['code' => $maliciousCode]);
        
        $this->assertEquals(200, $response['code']);
        $this->assertFalse($response['body']['success'], '应该防止SQL注入');
    }
}

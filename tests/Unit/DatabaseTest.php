<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Database;

/**
 * 数据库类单元测试
 */
class DatabaseTest extends TestCase
{
    private $db;
    private $config;

    protected function setUp(): void
    {
        $this->config = require __DIR__ . '/../bootstrap.php';
        $this->db = Database::getInstance($this->config['database']);
    }

    /**
     * 测试数据库单例模式
     */
    public function testSingletonInstance()
    {
        $db1 = Database::getInstance($this->config['database']);
        $db2 = Database::getInstance($this->config['database']);
        
        $this->assertSame($db1, $db2, '数据库应该是单例');
    }

    /**
     * 测试查询单条数据
     */
    public function testQueryOne()
    {
        // 查询默认访问码配置
        $result = $this->db->queryOne(
            "SELECT * FROM site_config WHERE config_key = ?",
            ['access_code']
        );
        
        $this->assertIsArray($result, '应该返回数组');
        $this->assertArrayHasKey('config_key', $result);
        $this->assertEquals('access_code', $result['config_key']);
    }

    /**
     * 测试查询多条数据
     */
    public function testQuery()
    {
        $results = $this->db->query("SELECT * FROM manga_types LIMIT 5");
        
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));
    }

    /**
     * 测试插入数据
     */
    public function testInsert()
    {
        $data = [
            'type_id' => 1,
            'tag_name' => 'Test Tag ' . time(),
            'tag_type' => 'category',
            'sort_order' => 0
        ];
        
        $id = $this->db->insert('tags', $data);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // 清理测试数据
        $this->db->delete('tags', 'id = ?', [$id]);
    }

    /**
     * 测试更新数据
     */
    public function testUpdate()
    {
        // 先插入测试数据
        $insertId = $this->db->insert('tags', [
            'type_id' => 1,
            'tag_name' => 'Original Name',
            'tag_type' => 'category',
            'sort_order' => 0
        ]);
        
        // 更新数据
        $result = $this->db->update(
            'tags',
            ['tag_name' => 'Updated Name'],
            'id = ?',
            [$insertId]
        );
        
        $this->assertNotFalse($result);
        
        // 验证更新
        $updated = $this->db->queryOne("SELECT * FROM tags WHERE id = ?", [$insertId]);
        $this->assertEquals('Updated Name', $updated['tag_name']);
        
        // 清理
        $this->db->delete('tags', 'id = ?', [$insertId]);
    }

    /**
     * 测试删除数据
     */
    public function testDelete()
    {
        // 插入测试数据
        $insertId = $this->db->insert('tags', [
            'type_id' => 1,
            'tag_name' => 'To Delete',
            'tag_type' => 'category',
            'sort_order' => 0
        ]);
        
        // 删除
        $result = $this->db->delete('tags', 'id = ?', [$insertId]);
        
        $this->assertTrue($result);
        
        // 验证删除
        $deleted = $this->db->queryOne("SELECT * FROM tags WHERE id = ?", [$insertId]);
        $this->assertNull($deleted);
    }

    /**
     * 测试分页查询
     */
    public function testPaginate()
    {
        $result = $this->db->paginate(
            "SELECT * FROM manga_types",
            [],
            1,
            5
        );
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertLessThanOrEqual(5, count($result['data']));
    }

    /**
     * 测试事务回滚
     */
    public function testTransactionRollback()
    {
        $this->db->beginTransaction();
        
        $insertId = $this->db->insert('tags', [
            'type_id' => 1,
            'tag_name' => 'Transaction Test',
            'tag_type' => 'category',
            'sort_order' => 0
        ]);
        
        $this->db->rollback();
        
        // 验证回滚
        $result = $this->db->queryOne("SELECT * FROM tags WHERE id = ?", [$insertId]);
        $this->assertNull($result, '事务回滚后数据不应存在');
    }

    /**
     * 测试事务提交
     */
    public function testTransactionCommit()
    {
        $this->db->beginTransaction();
        
        $insertId = $this->db->insert('tags', [
            'type_id' => 1,
            'tag_name' => 'Transaction Commit Test',
            'tag_type' => 'category',
            'sort_order' => 0
        ]);
        
        $this->db->commit();
        
        // 验证提交
        $result = $this->db->queryOne("SELECT * FROM tags WHERE id = ?", [$insertId]);
        $this->assertNotNull($result, '事务提交后数据应存在');
        
        // 清理
        $this->db->delete('tags', 'id = ?', [$insertId]);
    }

    /**
     * 测试预处理语句防SQL注入
     */
    public function testSqlInjectionPrevention()
    {
        // 尝试SQL注入
        $maliciousInput = "' OR '1'='1";
        
        $result = $this->db->queryOne(
            "SELECT * FROM site_config WHERE config_key = ?",
            [$maliciousInput]
        );
        
        $this->assertNull($result, '应该防止SQL注入，返回null');
    }

    protected function tearDown(): void
    {
        // 清理可能残留的测试数据
        $this->db->execute(
            "DELETE FROM tags WHERE tag_name LIKE 'Test%' OR tag_name LIKE '%Test'"
        );
    }
}

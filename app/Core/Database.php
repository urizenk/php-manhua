<?php
/**
 * C1-数据库管理模块
 * 提供PDO数据库连接和查询封装
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo = null;
    private $config = [];

    /**
     * 私有构造函数，防止外部实例化
     */
    private function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * 获取数据库单例
     */
    public static function getInstance($config = null)
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new \Exception('Database config is required');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * 建立数据库连接
     */
    private function connect()
    {
        try {
            // 兼容两种键名：dbname 和 database
            $dbname = $this->config['dbname'] ?? $this->config['database'] ?? '';
            
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $dbname,
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
        } catch (PDOException $e) {
            $this->handleError($e);
        }
    }

    /**
     * 获取PDO实例
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 执行查询（SELECT）
     * @param string $sql SQL语句
     * @param array $params 参数绑定
     * @return array 查询结果
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleError($e);
            return [];
        }
    }

    /**
     * 执行查询并返回单行
     */
    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * 执行写入操作（INSERT/UPDATE/DELETE）
     * @return int 影响的行数或插入的ID
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * 插入数据
     * @param string $table 表名
     * @param array $data 数据数组 ['field' => 'value']
     * @return int 插入的ID
     */
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $fields),
            implode(', ', $placeholders)
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * 更新数据
     * @param string $table 表名
     * @param array $data 数据数组
     * @param string $where WHERE条件
     * @param array $whereParams WHERE参数
     * @return int 影响的行数
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $sets = [];
        foreach (array_keys($data) as $field) {
            $sets[] = "`{$field}` = ?";
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $sets),
            $where
        );

        $params = array_merge(array_values($data), $whereParams);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where WHERE条件
     * @param array $params 参数
     * @return int 影响的行数
     */
    public function delete($table, $where, $params = [])
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * 开始事务
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    /**
     * 分页查询
     * @param string $table 表名
     * @param int $page 当前页
     * @param int $perPage 每页条数
     * @param string $where WHERE条件
     * @param array $params 参数
     * @param string $orderBy 排序
     * @return array ['data' => [], 'total' => 0, 'page' => 1, 'per_page' => 20, 'total_pages' => 0]
     */
    public function paginate($table, $page = 1, $perPage = 20, $where = '1=1', $params = [], $orderBy = 'id DESC')
    {
        // 计算总数
        $countSql = "SELECT COUNT(*) as total FROM `{$table}` WHERE {$where}";
        $countResult = $this->queryOne($countSql, $params);
        $total = $countResult ? (int)$countResult['total'] : 0;

        // 计算偏移量
        $offset = ($page - 1) * $perPage;

        // 查询数据
        $dataSql = "SELECT * FROM `{$table}` WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->query($dataSql, $params);

        return [
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * 错误处理
     */
    private function handleError(PDOException $e)
    {
        // 记录错误日志
        error_log('[Database Error] ' . $e->getMessage());
        
        // 开发环境直接抛出异常，生产环境返回友好提示
        if (defined('APP_DEBUG') && APP_DEBUG) {
            throw $e;
        } else {
            // 生产环境不暴露具体错误
            throw new \Exception('Database error occurred');
        }
    }

    /**
     * 防止克隆
     */
    private function __clone() {}
}



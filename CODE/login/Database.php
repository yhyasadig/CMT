<?php

class DatabaseConnection {
    private PDO $connection;

    // معلومات الاتصال
    private string $host = "localhost";
    private string $dbName = "task_management";
    private string $username = "root";
    private string $password = "";

    // المُنشئ أصبح عامًا الآن
    public function __construct() {
        $this->connect();
    }

    // إنشاء الاتصال
    private function connect(): void {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // للحصول على كائن الاتصال بـ PDO
    public function getConnection(): PDO {
        return $this->connection;
    }

    // التأكد من أن الاتصال موجود
    public function isConnected(): bool {
        return isset($this->connection);
    }
}

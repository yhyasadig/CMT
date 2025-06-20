<?php

class DatabaseConnection {
<<<<<<< HEAD
=======
    private static ?DatabaseConnection $instance = null; // الكائن الوحيد من الكلاس
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
    private PDO $connection;

    // معلومات الاتصال
    private string $host = "localhost";
    private string $dbName = "task_management";
    private string $username = "root";
    private string $password = "";

<<<<<<< HEAD
    // المُنشئ أصبح عامًا الآن
    public function __construct() {
=======
    // نجعل المُنشئ private لمنع الإنشاء من الخارج
    private function __construct() {
>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
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

<<<<<<< HEAD
=======
    // الدالة العامة للحصول على نفس الكائن (Singleton)
    public static function getInstance(): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

>>>>>>> 2c437069192c41dc67c3eef3ba98c09f930e22d9
    // للحصول على كائن الاتصال بـ PDO
    public function getConnection(): PDO {
        return $this->connection;
    }

    // التأكد من أن الاتصال موجود
    public function isConnected(): bool {
        return isset($this->connection);
    }
}

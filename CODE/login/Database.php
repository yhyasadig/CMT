<?php

class DatabaseConnection {
    private $host = "localhost";
    private $dbName = "task_management"; // تأكد من صحة اسم قاعدة البيانات
    private $username = "root"; // اسم المستخدم الافتراضي في XAMPP
    private $password = ""; // تأكد من كلمة المرور الصحيحة في phpMyAdmin
    private $connection;

    public function __construct() {
        $this->connect();  // إنشاء الاتصال بقاعدة البيانات عند إنشاء الكائن
    }

    private function connect() {
        try {
            // إعداد الاتصال باستخدام PDO
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);

            // تعيين خصائص الاتصال لإظهار الأخطاء
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // عرض الأخطاء في حالة الفشل
            die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // إرجاع الاتصال الفعلي باستخدام PDO
    public function getConnection() {
        return $this->connection;
    }

    // دالة للتأكد من أن الاتصال مفتوح
    public function isConnected() {
        return isset($this->connection);
    }
}

?>

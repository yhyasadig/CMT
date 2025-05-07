<?php

class DatabaseConnection {
    private $host = "localhost";
    private $dbName = "task_management"; // تأكد من صحة اسم قاعدة البيانات
    private $username = "root"; // اسم المستخدم الافتراضي في XAMPP
    private $password = ""; // تأكد من كلمة المرور الصحيحة في phpMyAdmin
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            // إعداد الاتصال باستخدام PDO
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);

            // تعيين خصائص الاتصال لإظهار الأخطاء
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // لا تعرض رسالة عند نجاح الاتصال
            // echo "تم الاتصال بقاعدة البيانات بنجاح!";
        } catch (PDOException $e) {
            // عرض الأخطاء في حالة الفشل
            die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // إرجاع الاتصال
    public function getConnection() {
        return $this->connection;
    }
}

// اختبار الاتصال بقاعدة البيانات
$dbConnection = new DatabaseConnection();
?>

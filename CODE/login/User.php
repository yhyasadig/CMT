<?php

require_once 'Database.php';

class User {
    private $connection;

    // تمرير الاتصال عند إنشاء الكائن
    public function __construct(DatabaseConnection $db) {
        $this->connection = $db->getConnection();
    }

    // تسجيل الدخول
    public function login($email, $password) {
        try {
            $query = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
            $query->bindParam(':email', $email);
            $query->execute();
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user; // المستخدم موجود وكلمة المرور صحيحة
            }

            return false; // كلمة المرور غير صحيحة أو البريد غير مسجّل
        } catch (PDOException $e) {
            // طباعة رسالة خطأ للملف السجل بدلاً من echo
            error_log("خطأ في تسجيل الدخول: " . $e->getMessage());
            return false;
        }
    }

    // إنشاء حساب مستخدم جديد
    public function create($name, $email, $password, $role, $projectId = null) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = $this->connection->prepare("
                INSERT INTO users (name, email, password, role, project_id)
                VALUES (:name, :email, :password, :role, :project_id)
            ");
            $query->bindParam(':name', $name);
            $query->bindParam(':email', $email);
            $query->bindParam(':password', $hashedPassword);
            $query->bindParam(':role', $role);
            $query->bindParam(':project_id', $projectId);
            return $query->execute(); // يرجع true إذا نجح التنفيذ
        } catch (PDOException $e) {
            // طباعة رسالة خطأ للملف السجل بدلاً من echo
            error_log("خطأ في إنشاء المستخدم: " . $e->getMessage());
            return false; // فشل في إنشاء المستخدم (ربما البريد موجود)
        }
    }

    // جلب بيانات مستخدم بناءً على user_id
    public function getUserById($userId) {
        try {
            $query = $this->connection->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $userId);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // طباعة رسالة خطأ للملف السجل بدلاً من echo
            error_log("خطأ في جلب المستخدم: " . $e->getMessage());
            return null;
        }
    }

    // حذف مستخدم (اختياري)
    public function delete($userId) {
        try {
            $query = $this->connection->prepare("DELETE FROM users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $userId);
            return $query->execute();
        } catch (PDOException $e) {
            // طباعة رسالة خطأ للملف السجل بدلاً من echo
            error_log("خطأ في حذف المستخدم: " . $e->getMessage());
            return false;
        }
    }
}

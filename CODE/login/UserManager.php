<?php
class UserManager {
    private $connection;

    public function __construct(DatabaseConnection $db) {
        $this->connection = $db->getConnection();
    }

    // تسجيل الدخول
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email";
        $params = [':email' => $email];
        $stmt = $this->executeQuery($query, $params);

        if ($stmt && $user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return password_verify($password, $user['password']) ? $user : false;
        }
        return false;
    }

    // إنشاء حساب مستخدم جديد
    public function create($name, $email, $password, $role, $projectId = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password, role, project_id) 
                  VALUES (:name, :email, :password, :role, :project_id)";
        $params = [
            ':name' => $name, ':email' => $email, ':password' => $hashedPassword, 
            ':role' => $role, ':project_id' => $projectId
        ];
        return $this->executeQuery($query, $params);
    }

    // جلب بيانات مستخدم بناءً على user_id
    public function getUserById($userId) {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    // حذف مستخدم بناءً على user_id
    public function delete($userId) {
        $query = "DELETE FROM users WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        return $this->executeQuery($query, $params);
    }

    // جلب كل الطلاب
    public function getAllStudents() {
        $query = "SELECT * FROM users WHERE role = 'student'";
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    // حذف طالب بناءً على user_id
    public function deleteStudentById($userId) {
        $query = "DELETE FROM users WHERE user_id = :user_id AND role = 'student'";
        $params = [':user_id' => $userId];
        return $this->executeQuery($query, $params);
    }

    // دالة تنفيذ الاستعلامات
    private function executeQuery($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return false;
        }
    }
}
?>

<?php
class NotificationManager {
    private $connection;

    // تمرير كائن الاتصال عند إنشاء الكائن
    public function __construct(DatabaseConnection $db) {
        $this->connection = $db->getConnection();
    }

    // جلب الإشعارات للمستخدم بناءً على user_id
    public function getNotifications($userId) {
        try {
            $query = "SELECT * FROM notifications WHERE user_id = :user_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب الإشعارات: " . $e->getMessage());
            return [];
        }
    }
}
?>

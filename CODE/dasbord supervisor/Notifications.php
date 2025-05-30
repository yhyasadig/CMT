<?php
class Notifications {
    private $connection;

    // مُنشئ الكلاس الذي يأخذ الاتصال بقاعدة البيانات
    public function __construct(DatabaseConnection $db) {
        $this->connection = $db->getConnection();
    }

    // دالة لإرسال إشعار للمستخدم
    public function sendNotification($userId, $message) {
        try {
            $query = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':message', $message);
            $stmt->execute();
        } catch (PDOException $e) {
            die("خطأ في إرسال الإشعار: " . $e->getMessage());
        }
    }

    // دالة لجلب إشعارات المستخدم (مقروءة وغير مقروءة)
    public function getNotifications($userId) {
        try {
            $query = "SELECT n.notification_id, n.message, n.created_at, n.is_read, 
                             p.name AS project_name, u.name AS sender_name 
                      FROM notifications n 
                      LEFT JOIN users u ON n.user_id = u.user_id 
                      LEFT JOIN projects p ON u.project_id = p.project_id 
                      WHERE n.user_id = :user_id 
                      ORDER BY n.created_at DESC";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطأ في جلب الإشعارات: " . $e->getMessage());
        }
    }

    // دالة لتحديث حالة الإشعار إلى "مقروء"
    public function markAsRead($notificationId) {
        try {
            $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = :notification_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            die("خطأ في تحديث حالة الإشعار: " . $e->getMessage());
        }
    }

    // دالة لحذف إشعار
    public function deleteNotification($notificationId) {
        try {
            $query = "DELETE FROM notifications WHERE notification_id = :notification_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            die("خطأ في حذف الإشعار: " . $e->getMessage());
        }
    }
}
?>

<?php
require_once 'Database.php';
require_once 'Notifications.php';

class Chat
{
    private $db;
    private $notification;

    // المُنشئ - يقوم بتهيئة الاتصال وقناة الإشعارات
    public function __construct()
    {
        try {
            $this->db = (new DatabaseConnection())->getConnection();
            $this->notification = new Notifications(new DatabaseConnection());
        } catch (PDOException $e) {
            die("Database connection failed in Chat class: " . $e->getMessage());
        }
    }

    // إرسال رسالة مع إشعارات لأعضاء المشروع (عدا المرسل)
    public function sendMessage(int $projectId, int $senderId, string $message): bool
    {
        try {
            // إدخال الرسالة في جدول الدردشة
            $stmt = $this->db->prepare("
                INSERT INTO chats (project_id, sender_id, message) 
                VALUES (:project_id, :sender_id, :message)
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':sender_id', $senderId);
            $stmt->bindParam(':message', $message);
            $stmt->execute();

            // جلب جميع المستخدمين في نفس المشروع عدا المُرسل
            $userQuery = $this->db->prepare("
                SELECT user_id FROM users 
                WHERE project_id = :project_id AND user_id != :sender_id
            ");
            $userQuery->bindParam(':project_id', $projectId);
            $userQuery->bindParam(':sender_id', $senderId);
            $userQuery->execute();
            $users = $userQuery->fetchAll(PDO::FETCH_COLUMN);

            // إرسال إشعار لكل مستخدم
            foreach ($users as $userId) {
                $this->notification->sendNotification($userId, "📨 رسالة جديدة في دردشة المشروع.");
            }

            return true;

        } catch (PDOException $e) {
            die("Error in sending message: " . $e->getMessage());
        }
    }

    // جلب كل الرسائل في مشروع معين
    public function getMessagesByProject(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.name AS sender_name, u.role 
                FROM chats c
                JOIN users u ON c.sender_id = u.user_id
                WHERE c.project_id = :project_id
                ORDER BY c.created_at ASC
            ");
            $stmt->bindParam(':project_id', $projectId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching chat messages: " . $e->getMessage());
        }
    }
}

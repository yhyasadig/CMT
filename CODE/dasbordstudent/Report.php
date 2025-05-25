<?php
require_once 'Database.php';
require_once 'Notifications.php';

class Report {
    private $db;
    private $notification;

    public function __construct(PDO $connection) {
        $this->db = $connection;
        $this->notification = new Notifications(new DatabaseConnection());
    }

    // ✅ إضافة تقرير جديد مع دعم الملف
    public function addReport($senderId, $receiverId, $userRole, $title, $body, $fileName = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO reports 
                (sender_id, receiver_id, user_role, report_title, report_body, file_name) 
                VALUES (:sender_id, :receiver_id, :user_role, :title, :body, :file_name)");

            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId,
                ':user_role' => $userRole,
                ':title' => $title,
                ':body' => $body,
                ':file_name' => $fileName
            ]);

            $message = "📄 تم إرسال تقرير جديد بعنوان: $title";
            $this->notification->sendNotification($receiverId, $message);

            return true;
        } catch (PDOException $e) {
            die("خطأ في إضافة التقرير: " . $e->getMessage());
        }
    }

    // ✅ جلب جميع التقارير المرسلة إلى مشرف معين
    public function getReportsForSupervisor($supervisorId) {
        $stmt = $this->db->prepare("SELECT r.*, u.name AS sender_name 
                                    FROM reports r 
                                    JOIN users u ON r.sender_id = u.user_id
                                    WHERE r.receiver_id = :receiver_id 
                                    ORDER BY r.created_at DESC");
        $stmt->execute([':receiver_id' => $supervisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ جلب تقارير مرسلة من مستخدم معين
    public function getReportsByUser($userId) {
        $stmt = $this->db->prepare("SELECT r.*, u.name AS receiver_name
                                    FROM reports r
                                    JOIN users u ON r.receiver_id = u.user_id
                                    WHERE sender_id = :user_id 
                                    ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ عرض تقرير واحد بالتفصيل
    public function getReportById($reportId) {
        $stmt = $this->db->prepare("SELECT r.*, 
                                           sender.name AS sender_name,
                                           receiver.name AS receiver_name
                                    FROM reports r
                                    JOIN users sender ON r.sender_id = sender.user_id
                                    JOIN users receiver ON r.receiver_id = receiver.user_id
                                    WHERE r.report_id = :report_id");
        $stmt->execute([':report_id' => $reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

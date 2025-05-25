<?php

class Comment {
    private $comment_id;   // معرّف التعليق
    private $task_id;      // معرّف المهمة التي ينتمي إليها التعليق
    private $user_id;      // معرّف المستخدم الذي أضاف التعليق
    private $comment_text; // نص التعليق
    private $timestamp;    // تاريخ ووقت إضافة التعليق

    // مُنشئ الكلاس لتحديد القيم الأولية
    public function __construct($comment_id = null, $task_id, $user_id, $comment_text, $timestamp = null) {
        $this->comment_id = $comment_id;
        $this->task_id = $task_id;
        $this->user_id = $user_id;
        $this->comment_text = $comment_text;
        $this->timestamp = $timestamp ? $timestamp : date('Y-m-d H:i:s'); // تعيين timestamp إلى الوقت الحالي إذا لم يُعطى
    }

    // دالة لإرجاع معرّف التعليق
    public function getCommentId() {
        return $this->comment_id;
    }

    // دالة لإرجاع معرّف المهمة
    public function getTaskId() {
        return $this->task_id;
    }

    // دالة لإرجاع معرّف المستخدم الذي أضاف التعليق
    public function getUserId() {
        return $this->user_id;
    }

    // دالة لإرجاع نص التعليق
    public function getCommentText() {
        return $this->comment_text;
    }

    // دالة لإرجاع تاريخ ووقت إضافة التعليق
    public function getTimestamp() {
        return $this->timestamp;
    }

    // دالة لحفظ التعليق في قاعدة البيانات
    public function saveToDatabase($db) {
        // التحقق أولاً إذا كان المستخدم مكلفًا بالمهمة أو مشرف
        $query = "SELECT * FROM tasks WHERE task_id = :task_id AND (assigned_to = :user_id OR :role = 'supervis')";
        $stmt = $db->getConnection()->prepare($query); // استخدام الاتصال PDO هنا
        $stmt->bindParam(':task_id', $this->task_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':role', $_SESSION['role']); // إذا كان المستخدم مشرف
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // إذا كان المستخدم مكلفًا بالمهمة أو مشرف، نكمل إضافة التعليق
            $query = "INSERT INTO comments (task_id, user_id, comment_text, timestamp) 
                      VALUES (:task_id, :user_id, :comment_text, :timestamp)";
            $stmt = $db->getConnection()->prepare($query); // استخدام الاتصال PDO هنا
            $stmt->bindParam(':task_id', $this->task_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':comment_text', $this->comment_text);
            $stmt->bindParam(':timestamp', $this->timestamp);
            return $stmt->execute();
        } else {
            // إذا كان المستخدم غير مكلف بالمهمة ولا يمتلك صلاحية المشرف، إرجاع خطأ
            return "Error: User is not authorized to comment on this task.";
        }
    }

    // دالة لاسترجاع التعليقات المرتبطة بمهمة معينة مع اسم المستخدم
    public static function getCommentsByTaskId($db, $task_id) {
        // التعديل هنا لجلب اسم المستخدم بدلاً من الرقم
        $query = "SELECT comments.*, users.name AS user_name FROM comments 
                  JOIN users ON comments.user_id = users.user_id 
                  WHERE comments.task_id = :task_id 
                  ORDER BY comments.timestamp DESC";
        $stmt = $db->getConnection()->prepare($query); // استخدام الاتصال PDO هنا
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // دالة لتحديث نص التعليق
    public function updateCommentText($db, $newText) {
        try {
            $this->comment_text = $newText;
            $query = "UPDATE comments SET comment_text = :comment_text WHERE comment_id = :comment_id";
            $stmt = $db->getConnection()->prepare($query); // استخدام الاتصال PDO هنا
            $stmt->bindParam(':comment_text', $this->comment_text);
            $stmt->bindParam(':comment_id', $this->comment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error updating comment: " . $e->getMessage();
            return false;
        }
    }

    // دالة لحذف التعليق
    public function deleteComment($db) {
        try {
            $query = "DELETE FROM comments WHERE comment_id = :comment_id";
            $stmt = $db->getConnection()->prepare($query); // استخدام الاتصال PDO هنا
            $stmt->bindParam(':comment_id', $this->comment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error deleting comment: " . $e->getMessage();
            return false;
        }
    }
}
?>

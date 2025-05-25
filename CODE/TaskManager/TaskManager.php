<?php

class TaskManager {
    private $db; // اتصال قاعدة البيانات

    public function __construct($db) {
        $this->db = $db;
    }

    //  تعديل: إضافة مهمة جديدة مع إشعار وربط notification_id بالمهمة
    public function addTask($projectId, $title, $description, $assignedTo, $dueDate) {
        try {
            // 1. إرسال إشعار للمستخدم
            $notificationMessage = "تم تعيين مهمة جديدة: " . $title;
            $notificationQuery = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
            $stmt1 = $this->db->prepare($notificationQuery);
            $stmt1->bindParam(':user_id', $assignedTo);
            $stmt1->bindParam(':message', $notificationMessage);
            $stmt1->execute();

            // 2. الحصول على معرف الإشعار المضاف
            $notificationId = $this->db->lastInsertId();

            // 3. إدخال المهمة وربطها بالإشعار
            $taskQuery = "INSERT INTO tasks (project_id, title, description, assigned_to, due_date, status, notification_id) 
                          VALUES (:project_id, :title, :description, :assigned_to, :due_date, 'pending', :notification_id)";
            $stmt2 = $this->db->prepare($taskQuery);
            $stmt2->bindParam(':project_id', $projectId);
            $stmt2->bindParam(':title', $title);
            $stmt2->bindParam(':description', $description);
            $stmt2->bindParam(':assigned_to', $assignedTo);
            $stmt2->bindParam(':due_date', $dueDate);
            $stmt2->bindParam(':notification_id', $notificationId);
            return $stmt2->execute();
        } catch (Exception $e) {
            echo "Error adding task with notification: " . $e->getMessage();
            return false;
        }
    }

    // جلب المهام الخاصة بالمشروع
    public function getTasksByProject($projectId) {
        try {
            $query = "SELECT t.*, p.name AS project_name, p.description AS project_description
                      FROM tasks t
                      JOIN projects p ON t.project_id = p.project_id
                      WHERE t.project_id = :project_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching tasks: " . $e->getMessage();
            return false;
        }
    }

    // جلب المستخدمين المرتبطين بنفس project_id
    public function getUsersByProject($projectId) {
        try {
            $query = "SELECT * FROM users WHERE project_id = :project_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching users: " . $e->getMessage();
            return false;
        }
    }

    // دالة لاسترجاع المهمة باستخدام task_id
    public function getTaskById($taskId) {
        try {
            $query = "SELECT * FROM tasks WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching task by ID: " . $e->getMessage();
            return false;
        }
    }

    // دالة لاسترجاع التعليقات المرتبطة بمهمة معينة مع اسم المستخدم
    public function getCommentsByTask($taskId) {
        try {
            // تعديل الاستعلام ليشمل اسم المستخدم من جدول users
            $query = "SELECT comments.*, users.name AS user_name FROM comments 
                      JOIN users ON comments.user_id = users.user_id 
                      WHERE comments.task_id = :task_id 
                      ORDER BY comments.timestamp DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // التأكد من أن الاستعلام يعيد بيانات المستخدم
        } catch (Exception $e) {
            echo "Error fetching comments: " . $e->getMessage();
            return false;
        }
    }

    // دالة لإضافة تعليق
    public function addComment($taskId, $userId, $commentText) {
        try {
            $query = "INSERT INTO comments (task_id, user_id, comment_text) VALUES (:task_id, :user_id, :comment_text)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':comment_text', $commentText);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error adding comment: " . $e->getMessage();
            return false;
        }
    }

    // دالة لتحديث نص التعليق
    public function updateCommentText($commentId, $newText) {
        try {
            $query = "UPDATE comments SET comment_text = :comment_text WHERE comment_id = :comment_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':comment_text', $newText);
            $stmt->bindParam(':comment_id', $commentId);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error updating comment: " . $e->getMessage();
            return false;
        }
    }

    // دالة لحذف التعليق
    public function deleteComment($commentId) {
        try {
            $query = "DELETE FROM comments WHERE comment_id = :comment_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':comment_id', $commentId);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error deleting comment: " . $e->getMessage();
            return false;
        }
    }

    // دالة لتحديث حالة المهمة
    public function updateTaskStatus($taskId, $status) {
        try {
            $query = "UPDATE tasks SET status = :status WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':task_id', $taskId);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error updating task status: " . $e->getMessage();
            return false;
        }
    }

    // دالة لحذف المهمة
    public function deleteTask($taskId) {
        try {
            $query = "DELETE FROM tasks WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error deleting task: " . $e->getMessage();
            return false;
        }
    }

    // دالة لجلب الملفات المرتبطة بمهمة معينة
    public function getFilesByTask($taskId) {
        try {
            $query = "SELECT * FROM files WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching files: " . $e->getMessage();
            return false;
        }
    }

    // دالة للبحث عن المهام حسب project_id و term (المصطلح)
    public function searchTasksByProjectAndTerm($projectId, $term) {
        try {
            $query = "SELECT * FROM tasks WHERE project_id = :project_id AND (title LIKE :term OR description LIKE :term)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindValue(':term', '%' . $term . '%');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error searching tasks: " . $e->getMessage();
            return false;
        }
    }
}
?>


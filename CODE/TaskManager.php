<?php
class TaskManager {
    private $db; // متغير لتخزين اتصال قاعدة البيانات

    // بناء الكلاس مع تمرير الاتصال بقاعدة البيانات
    public function __construct($db) {
        $this->db = $db;
    }

    // إضافة مهمة جديدة
    public function addTask($projectId, $title, $description, $assignedTo, $dueDate) {
        try {
            $query = "INSERT INTO tasks (project_id, title, description, assigned_to, due_date, status) 
                      VALUES (:project_id, :title, :description, :assigned_to, :due_date, 'pending')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':assigned_to', $assignedTo);
            $stmt->bindParam(':due_date', $dueDate);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error adding task: " . $e->getMessage();
            return false;
        }
    }

    // الحصول على جميع المهام المتعلقة بمشروع معين
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

    // جلب الأعضاء المرتبطين بالمشروع
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

    // الحصول على تفاصيل مهمة معينة بناءً على task_id
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

    // تحديث مهمة معينة بناءً على task_id
    public function updateTask($taskId, $title, $description, $assignedTo, $dueDate, $status) {
        try {
            $query = "UPDATE tasks SET title = :title, description = :description, assigned_to = :assigned_to, due_date = :due_date, status = :status WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':assigned_to', $assignedTo);
            $stmt->bindParam(':due_date', $dueDate);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':task_id', $taskId);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error updating task: " . $e->getMessage();
            return false;
        }
    }

    // تحديث حالة المهمة (مثال: قيد التنفيذ أو مكتملة)
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

    // حذف مهمة
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

    // جلب الملفات المرتبطة بكل مهمة
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

    // جلب التعليقات المرتبطة بكل مهمة
    public function getCommentsByTask($taskId) {
        try {
            $query = "SELECT * FROM comments WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching comments: " . $e->getMessage();
            return false;
        }
    }

    // إضافة تعليق إلى المهمة
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

    // إضافة ملف إلى المهمة
    public function addFile($taskId, $fileName, $uploadedBy) {
        try {
            $query = "INSERT INTO files (task_id, file_name, uploaded_by) VALUES (:task_id, :file_name, :uploaded_by)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':uploaded_by', $uploadedBy);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error adding file: " . $e->getMessage();
            return false;
        }
    }
}
?>

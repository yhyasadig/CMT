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
            $query = "SELECT * FROM tasks WHERE project_id = :project_id";
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);  // إرجاع البيانات في شكل مصفوفة
        } catch (Exception $e) {
            echo "Error fetching users: " . $e->getMessage();
            return false;
        }
    }

    // الحصول على تفاصيل مهمة معينة
    public function getTaskDetails($taskId) {
        try {
            $query = "SELECT * FROM tasks WHERE task_id = :task_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error fetching task details: " . $e->getMessage();
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
}
?>

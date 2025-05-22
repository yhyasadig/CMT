<?php

class Project {
    private $projectId;
    private $projectName;
    private $projectDescription;
    private $startDate;
    private $endDate;
    private $leaderId;
    private $connection;

    // مُنشئ الكلاس
    public function __construct(DatabaseConnection $db, $projectId) {
        $this->connection = $db->getConnection(); // الاتصال بقاعدة البيانات
        $this->projectId = $projectId;
    }

    // دالة لجلب تفاصيل المشروع
    public function getProjectDetails() {
        try {
            $query = "SELECT * FROM projects WHERE project_id = :project_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':project_id', $this->projectId, PDO::PARAM_INT);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                $this->projectName = $project['name'];
                $this->projectDescription = $project['description'];
                $this->startDate = $project['start_date'];
                $this->endDate = $project['end_date'];
                $this->leaderId = $project['leader_id'];
            }

            return $project;
        } catch (PDOException $e) {
            die("حدث خطأ أثناء جلب تفاصيل المشروع: " . $e->getMessage());
        }
    }

    // دالة لتحديث بيانات المشروع (كل الحقول)
    public function updateProjectDetails() {
        try {
            $query = "UPDATE projects 
                      SET name = :name, description = :description, start_date = :start_date, end_date = :end_date, leader_id = :leader_id 
                      WHERE project_id = :project_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':name', $this->projectName);
            $stmt->bindParam(':description', $this->projectDescription);
            $stmt->bindParam(':start_date', $this->startDate);
            $stmt->bindParam(':end_date', $this->endDate);
            $stmt->bindParam(':leader_id', $this->leaderId);
            $stmt->bindParam(':project_id', $this->projectId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("حدث خطأ أثناء تحديث المشروع: " . $e->getMessage());
        }
    }

    // دالة لإضافة مهمة للمشروع
    public function addTask($taskName, $taskDescription, $assignedTo, $dueDate) {
        try {
            $query = "INSERT INTO tasks (project_id, task_name, task_description, assigned_to, due_date, status) 
                      VALUES (:projectId, :taskName, :taskDescription, :assignedTo, :dueDate, 'pending')";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':projectId', $this->projectId);
            $stmt->bindParam(':taskName', $taskName);
            $stmt->bindParam(':taskDescription', $taskDescription);
            $stmt->bindParam(':assignedTo', $assignedTo);
            $stmt->bindParam(':dueDate', $dueDate);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("حدث خطأ أثناء إضافة المهمة: " . $e->getMessage());
        }
    }

    // دالة لعرض المهام الخاصة بالمشروع
    public function getTasks() {
        try {
            $query = "SELECT * FROM tasks WHERE project_id = :projectId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':projectId', $this->projectId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("حدث خطأ أثناء استرجاع المهام: " . $e->getMessage());
        }
    }

    // دالة لإضافة عضو للمشروع
    public function addMember($userId) {
        try {
            $query = "UPDATE users SET project_id = :projectId WHERE user_id = :userId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':projectId', $this->projectId);
            $stmt->bindParam(':userId', $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("حدث خطأ أثناء إضافة العضو: " . $e->getMessage());
        }
    }

    // دالة لعرض قائد الفريق
    public function getLeader() {
        try {
            $query = "SELECT name FROM users WHERE user_id = :leaderId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':leaderId', $this->leaderId);
            $stmt->execute();
            $leader = $stmt->fetch(PDO::FETCH_ASSOC);
            return $leader['name'];
        } catch (PDOException $e) {
            die("حدث خطأ أثناء استرجاع قائد الفريق: " . $e->getMessage());
        }
    }

    // دالة لتحديث المشروع
    public function updateProject($newEndDate) {
        try {
            $query = "UPDATE projects SET end_date = :newEndDate WHERE project_id = :projectId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':newEndDate', $newEndDate);
            $stmt->bindParam(':projectId', $this->projectId);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("حدث خطأ أثناء تحديث المشروع: " . $e->getMessage());
        }
    }

    // دالة لحذف المشروع
    public function deleteProject() {
        try {
            $query = "DELETE FROM projects WHERE project_id = :projectId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':projectId', $this->projectId);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("حدث خطأ أثناء حذف المشروع: " . $e->getMessage());
        }
    }

    // Getter and Setter for project properties
    public function setProjectName($name) {
        $this->projectName = $name;
    }

    public function setProjectDescription($description) {
        $this->projectDescription = $description;
    }

    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    public function setLeaderId($leaderId) {
        $this->leaderId = $leaderId;
    }

    public function getProjectId() {
        return $this->projectId;
    }

    public function getProjectName() {
        return $this->projectName;
    }

    public function getProjectDescription() {
        return $this->projectDescription;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function getLeaderId() {
        return $this->leaderId;
    }
}
?>

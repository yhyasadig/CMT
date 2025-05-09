<?php
require_once 'User.php'; // تأكد من أن كلاس User موجود ويتم استيراده هنا

class TeamMember extends User {
    private $teamMemberId;
    private $projectId;
    private $role;

    public function __construct(DatabaseConnection $db, $teamMemberId, $projectId, $role) {
        parent::__construct($db);  // استدعاء المُنشئ من كلاس User لضبط الاتصال
        $this->teamMemberId = $teamMemberId;
        $this->projectId = $projectId;
        $this->role = $role;
    }

    // دالة لإضافة العضو إلى المشروع
    public function addToProject() {
        try {
            // استعلام لإضافة العضو إلى الفريق في جدول team_members
            $query = "INSERT INTO team_members (user_id, project_id, role) VALUES (:user_id, :project_id, :role)";
            $stmt = $this->getConnection()->prepare($query);  // استخدام دالة getConnection() من User للوصول إلى الاتصال
            $stmt->bindParam(':user_id', $this->teamMemberId);
            $stmt->bindParam(':project_id', $this->projectId);
            $stmt->bindParam(':role', $this->role);
            return $stmt->execute();  // تنفيذ الاستعلام وإرجاع النتيجة (صواب أو خطأ)
        } catch (PDOException $e) {
            die("حدث خطأ أثناء إضافة العضو للمشروع: " . $e->getMessage());
        }
    }

    // دالة لعرض المشروع المعين لهذا العضو
    public function viewAssignedProject() {
        return "المشروع المعين لهذا العضو هو: " . $this->projectId;
    }

    // دالة لتحديث دور العضو في المشروع
    public function updateRole($newRole) {
        $this->role = $newRole;
        return "تم تحديث دور العضو إلى: " . $this->role;
    }

    // دالة لعرض الدور الحالي للعضو
    public function getRole() {
        return $this->role;
    }

    // دالة لضبط الدور
    public function setRole($role) {
        $this->role = $role;
    }

    // دالة لجلب معرف العضو
    public function getTeamMemberId() {
        return $this->teamMemberId;
    }

    // دالة لضبط معرف العضو
    public function setTeamMemberId($teamMemberId) {
        $this->teamMemberId = $teamMemberId;
    }

    // دالة لجلب جميع أعضاء الفريق لمشروع معين
    public function getTeamMembers($projectId) {
        // استخدام دالة getConnection() من User للوصول إلى الاتصال
        $query = "SELECT u.user_id, u.name, u.email, tm.role FROM team_members tm 
                  JOIN users u ON tm.user_id = u.user_id
                  WHERE tm.project_id = :project_id";
        $stmt = $this->getConnection()->prepare($query);  // استخدام getConnection() هنا
        $stmt->bindParam(":project_id", $projectId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // جلب الأعضاء المرتبطين بالمشروع
    }
}
?>

<?php

require_once 'User.php'; // تأكد من أن كلاس User موجود ويتم استيراده هنا

class TeamMember extends User {
    private $teamMemberId;
    private $projectId;
    private $role;

    // مُنشئ الكلاس
    public function __construct(DatabaseConnection $db, $userId, $name, $email, $password, $teamMemberId, $projectId, $role) {
        parent::__construct($db); // استدعاء المُنشئ من كلاس User
        $this->teamMemberId = $teamMemberId;
        $this->projectId = $projectId;
        $this->role = $role;
    }

    // دالة لعرض المشروع المعين لهذا العضو
    public function viewAssignedProject() {
        // هذا مجرد مثال، يجب عليك إضافة منطق جلب البيانات من قاعدة البيانات
        return "المشروع المعين لهذا العضو هو: " . $this->projectId;
    }

    // دالة لتحديث دور العضو في المشروع
    public function updateRole($newRole) {
        $this->role = $newRole;
        // يمكنك هنا إضافة منطق لتحديث الدور في قاعدة البيانات أيضًا
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
}
?>

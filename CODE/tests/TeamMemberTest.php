<?php
use PHPUnit\Framework\TestCase;

require_once 'TeamMember.php';

// محاكي لكائن PDOStatement
class MockPDOStatement {
    public function bindParam($param, $value) {}
    public function execute() { return true; }
    public function fetchAll($mode = null) {
        return [
            ['user_id' => 1, 'name' => 'Ahmed', 'email' => 'ahmed@example.com', 'role' => 'member'],
            ['user_id' => 2, 'name' => 'Sara', 'email' => 'sara@example.com', 'role' => 'leader']
        ];
    }
}

// محاكي لكائن PDO
class MockPDO {
    public function prepare($query) {
        return new MockPDOStatement();
    }
}

// محاكي للاتصال بقاعدة البيانات
class MockDatabaseConnection {
    public function getConnection() {
        return new MockPDO();
    }
}

// محاكي لكلاس User الذي يرث منه TeamMember
class MockUser extends User {
    protected $connection;

    public function __construct($db) {
        $this->connection = $db->getConnection();
    }

    public function getConnection() {
        return $this->connection;
    }
}

// إعادة تعريف TeamMember بوراثة MockUser بدل User الحقيقي
class TestableTeamMember extends MockUser {
    private $teamMemberId;
    private $projectId;
    private $role;

    public function __construct($db, $userId, $projectId, $role) {
        parent::__construct($db);
        $this->teamMemberId = $userId;
        $this->projectId = $projectId;
        $this->role = $role;
    }

    public function addToProject() {
        $query = "INSERT INTO team_members (user_id, project_id, role) VALUES (:user_id, :project_id, :role)";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':user_id', $this->teamMemberId);
        $stmt->bindParam(':project_id', $this->projectId);
        $stmt->bindParam(':role', $this->role);
        return $stmt->execute();
    }

    public function viewAssignedProject() {
        return "📌 المشروع المعين للعضو هو: " . $this->projectId;
    }

    public function updateRole($newRole) {
        $this->role = $newRole;
        return "✅ تم تحديث الدور إلى: " . $this->role;
    }

    public function getRole() {
        return $this->role;
    }

    public function getTeamMembers($projectId) {
        $query = "SELECT u.user_id, u.name, u.email, tm.role FROM team_members tm 
                  JOIN users u ON tm.user_id = u.user_id
                  WHERE tm.project_id = :project_id";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":project_id", $projectId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class TeamMemberTest extends TestCase {
    private $teamMember;

    protected function setUp(): void {
        $mockDb = new MockDatabaseConnection();
        $this->teamMember = new TestableTeamMember($mockDb, 1, 101, 'member');
    }

    public function testAddToProject() {
        $this->assertTrue($this->teamMember->addToProject());
    }

    public function testViewAssignedProject() {
        $this->assertEquals('📌 المشروع المعين للعضو هو: 101', $this->teamMember->viewAssignedProject());
    }

    public function testUpdateRole() {
        $result = $this->teamMember->updateRole('leader');
        $this->assertEquals('✅ تم تحديث الدور إلى: leader', $result);
    }

    public function testGetRole() {
        $this->teamMember->updateRole('tester');
        $this->assertEquals('tester', $this->teamMember->getRole());
    }

    public function testGetTeamMembers() {
        $members = $this->teamMember->getTeamMembers(101);
        $this->assertCount(2, $members);
        $this->assertEquals('Ahmed', $members[0]['name']);
        $this->assertEquals('Sara', $members[1]['name']);
    }
}

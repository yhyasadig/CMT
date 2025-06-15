<?php
use PHPUnit\Framework\TestCase;

require_once 'Project.php';

class MockPDOStatement {
    public function bindParam($param, &$value, $type = null) {}
    public function execute() { return true; }
    public function fetch($mode = null) {
        return ['name' => 'Test Leader', 'description' => 'A project', 'start_date' => '2024-01-01', 'end_date' => '2024-06-01', 'leader_id' => 2];
    }
    public function fetchAll($mode = null) {
        return [['task_name' => 'Task 1'], ['task_name' => 'Task 2']];
    }
}

class MockPDO {
    public function prepare($query) {
        return new MockPDOStatement();
    }
}

class DatabaseConnection {
    public function getConnection() {
        return new MockPDO();
    }
}

class ProjectTest extends TestCase {
    public function testSettersAndGetters() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $project->setProjectName("My Project");
        $project->setProjectDescription("Test Desc");
        $project->setStartDate("2024-01-01");
        $project->setEndDate("2024-06-01");
        $project->setLeaderId(5);

        $this->assertEquals("My Project", $project->getProjectName());
        $this->assertEquals("Test Desc", $project->getProjectDescription());
        $this->assertEquals("2024-01-01", $project->getStartDate());
        $this->assertEquals("2024-06-01", $project->getEndDate());
        $this->assertEquals(5, $project->getLeaderId());
    }

    public function testGetProjectDetails() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $details = $project->getProjectDetails();

        $this->assertIsArray($details);
        $this->assertEquals('A project', $details['description']);
    }

    public function testUpdateProject() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $result = $project->updateProject("2024-12-31");

        $this->assertTrue($result);
    }

    public function testAddTask() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $result = $project->addTask("Task 1", "Desc", 2, "2024-05-01");

        $this->assertTrue($result);
    }

    public function testGetTasks() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $tasks = $project->getTasks();

        $this->assertIsArray($tasks);
        $this->assertEquals("Task 1", $tasks[0]['task_name']);
    }

    public function testGetLeader() {
        $db = new DatabaseConnection();
        $project = new Project($db, 1);
        $project->setLeaderId(2);

        $leaderName = $project->getLeader();
        $this->assertEquals("Test Leader", $leaderName);
    }
}
?>

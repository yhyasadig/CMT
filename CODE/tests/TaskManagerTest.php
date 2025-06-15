<?php
use PHPUnit\Framework\TestCase;
require_once 'TaskManager.php';

class TaskManagerTest extends TestCase
{
    private $dbMock;
    private $stmtMock;
    private $taskManager;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->taskManager = new TaskManager($this->dbMock);
    }

    public function testAddTask()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);
        
        // ✅ تعديل هنا: lastInsertId ترجع string وليس int
        $this->dbMock->method('lastInsertId')->willReturn("10");

        $result = $this->taskManager->addTask(1, "Test Task", "Description", 2, "2025-06-30");
        $this->assertTrue($result);
    }

    public function testGetTasksByProject()
    {
        $this->stmtMock->method('fetchAll')->willReturn([['title' => 'Task A']]);
        $this->stmtMock->method('execute');
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->getTasksByProject(1);
        $this->assertIsArray($result);
    }

    public function testGetUsersByProject()
    {
        $this->stmtMock->method('fetchAll')->willReturn([['name' => 'User A']]);
        $this->stmtMock->method('execute');
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->getUsersByProject(1);
        $this->assertIsArray($result);
    }

    public function testGetTaskById()
    {
        $this->stmtMock->method('fetch')->willReturn(['title' => 'Task A']);
        $this->stmtMock->method('execute');
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->getTaskById(5);
        $this->assertEquals('Task A', $result['title']);
    }

    public function testAddComment()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->addComment(1, 2, "Nice work!");
        $this->assertTrue($result);
    }

    public function testUpdateCommentText()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->updateCommentText(1, "Updated text");
        $this->assertTrue($result);
    }

    public function testDeleteComment()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->deleteComment(1);
        $this->assertTrue($result);
    }

    public function testUpdateTaskStatus()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->updateTaskStatus(1, "completed");
        $this->assertTrue($result);
    }

    public function testDeleteTask()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->deleteTask(1);
        $this->assertTrue($result);
    }

    public function testGetFilesByTask()
    {
        $this->stmtMock->method('fetchAll')->willReturn([['file_name' => 'doc.pdf']]);
        $this->stmtMock->method('execute');
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->getFilesByTask(1);
        $this->assertIsArray($result);
    }

    public function testSearchTasksByProjectAndTerm()
    {
        $this->stmtMock->method('fetchAll')->willReturn([['title' => 'Search Result']]);
        $this->stmtMock->method('execute');
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->searchTasksByProjectAndTerm(1, 'search');
        $this->assertIsArray($result);
    }

    public function testUpdateTask()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->taskManager->updateTask(1, "New Title", "Updated Desc", 3, "2025-07-01", "in progress");
        $this->assertTrue($result);
    }
}

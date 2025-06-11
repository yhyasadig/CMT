<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../User.php';
require_once __DIR__ . '/../Database.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE users (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                email TEXT UNIQUE,
                password TEXT,
                role TEXT,
                project_id INTEGER
            )
        ");

        $mockDb = $this->createMock(DatabaseConnection::class);
        $mockDb->method('getConnection')->willReturn($this->pdo);
        $this->user = new User($mockDb);
    }

    public function testCreateUser()
    {
        $result = $this->user->create('Ali', 'ali@example.com', '123456', 'student', 1);
        $this->assertTrue($result);
    }

    public function testLoginUser()
    {
        $password = password_hash('mypassword', PASSWORD_DEFAULT);
        $this->pdo->exec("
            INSERT INTO users (name, email, password, role, project_id)
            VALUES ('User1', 'user1@test.com', '$password', 'student', 1)
        ");

        $result = $this->user->login('user1@test.com', 'mypassword');
        $this->assertIsArray($result);
        $this->assertEquals('user1@test.com', $result['email']);
    }

    public function testGetUserById()
    {
        $this->pdo->exec("
            INSERT INTO users (name, email, password, role, project_id)
            VALUES ('User2', 'user2@test.com', 'pass', 'student', 1)
        ");
        $userId = $this->pdo->lastInsertId();

        $result = $this->user->getUserById($userId);
        $this->assertIsArray($result);
        $this->assertEquals('User2', $result['name']);
    }

    public function testDeleteUser()
    {
        $this->pdo->exec("
            INSERT INTO users (name, email, password, role, project_id)
            VALUES ('DeleteUser', 'delete@test.com', 'pass', 'student', 1)
        ");
        $userId = $this->pdo->lastInsertId();

        $result = $this->user->delete($userId);
        $this->assertTrue($result);
    }

    public function testGetAllStudents()
    {
        $this->pdo->exec("
            INSERT INTO users (name, email, password, role, project_id)
            VALUES 
            ('Student1', 's1@test.com', 'p1', 'student', 1),
            ('Teacher1', 't1@test.com', 'p2', 'teacher', 1)
        ");

        $students = $this->user->getAllStudents();
        $this->assertCount(1, $students);
        $this->assertEquals('Student1', $students[0]['name']);
    }

    public function testDeleteStudentById()
    {
        $this->pdo->exec("
            INSERT INTO users (name, email, password, role, project_id)
            VALUES ('StudentToDelete', 'stdel@test.com', 'pw', 'student', 1)
        ");
        $userId = $this->pdo->lastInsertId();

        $result = $this->user->deleteStudentById($userId);
        $this->assertTrue($result);
    }
}

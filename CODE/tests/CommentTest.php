<?php
use PHPUnit\Framework\TestCase;

require_once 'Comment.php';

class MockPDOStatement {
    public function bindParam($param, &$var) {}
    public function execute() { return true; }
    public function rowCount() { return 1; }
    public function fetchAll($mode = null) {
        return [['comment_text' => 'Test comment', 'user_name' => 'John Doe']];
    }
}

class MockPDO {
    public function prepare($query) {
        return new MockPDOStatement();
    }
}

class MockDB {
    public function getConnection() {
        return new MockPDO();
    }
}

class CommentTest extends TestCase {
    public function testGetters() {
        $comment = new Comment(1, 2, 3, "Test comment", "2024-01-01 12:00:00");
        $this->assertEquals(1, $comment->getCommentId());
        $this->assertEquals(2, $comment->getTaskId());
        $this->assertEquals(3, $comment->getUserId());
        $this->assertEquals("Test comment", $comment->getCommentText());
        $this->assertEquals("2024-01-01 12:00:00", $comment->getTimestamp());
    }

    public function testSaveToDatabase() {
        $_SESSION['role'] = 'supervis'; // mock session role
        $comment = new Comment(null, 2, 3, "Mock comment");
        $mockDb = new MockDB();
        $result = $comment->saveToDatabase($mockDb);
        $this->assertTrue($result);
    }

    public function testUpdateCommentText() {
        $comment = new Comment(1, 2, 3, "Old comment");
        $mockDb = new MockDB();
        $result = $comment->updateCommentText($mockDb, "Updated comment");
        $this->assertTrue($result);
    }

    public function testDeleteComment() {
        $comment = new Comment(1, 2, 3, "Comment to delete");
        $mockDb = new MockDB();
        $result = $comment->deleteComment($mockDb);
        $this->assertTrue($result);
    }

    public function testGetCommentsByTaskId() {
        $mockDb = new MockDB();
        $result = Comment::getCommentsByTaskId($mockDb, 2);
        $this->assertIsArray($result);
        $this->assertEquals('Test comment', $result[0]['comment_text']);
        $this->assertEquals('John Doe', $result[0]['user_name']);
    }
}
?>
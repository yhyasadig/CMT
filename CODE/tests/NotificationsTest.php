<?php
use PHPUnit\Framework\TestCase;

// تضمين الكلاس الذي نختبره
require_once 'Notifications.php';

// ⚠️ في حال عدم وجود كلاس قاعدة البيانات، يمكنك إنشاؤه أو التعديل على هذا:
class MockPDOStatement {
    public function bindParam($param, &$var, $type = null) {}
    public function execute() { return true; }
    public function fetchAll($mode = null) {
        return [
            [
                'notification_id' => 1,
                'message' => 'Test message',
                'created_at' => '2024-01-01 00:00:00',
                'is_read' => 0,
                'project_name' => 'Alpha',
                'sender_name' => 'Admin'
            ]
        ];
    }
}

class MockPDO {
    public function prepare($query) {
        return new MockPDOStatement();
    }
}

// هذا الكلاس يجب أن يكون نفس اسم كلاس الاتصال الحقيقي
class DatabaseConnection {
    public function getConnection() {
        return new MockPDO();
    }
}

class NotificationsTest extends TestCase {
    public function testSendNotification() {
        $db = new DatabaseConnection();
        $notifications = new Notifications($db);

        $this->expectNotToPerformAssertions();
        $notifications->sendNotification(1, "Hello User");
    }

    public function testGetNotifications() {
        $db = new DatabaseConnection();
        $notifications = new Notifications($db);

        $result = $notifications->getNotifications(1);

        $this->assertIsArray($result);
        $this->assertEquals('Test message', $result[0]['message']);
        $this->assertEquals('Alpha', $result[0]['project_name']);
        $this->assertEquals('Admin', $result[0]['sender_name']);
    }

    public function testMarkAsRead() {
        $db = new DatabaseConnection();
        $notifications = new Notifications($db);

        $this->expectNotToPerformAssertions();
        $notifications->markAsRead(1);
    }

    public function testDeleteNotification() {
        $db = new DatabaseConnection();
        $notifications = new Notifications($db);

        $this->expectNotToPerformAssertions();
        $notifications->deleteNotification(1);
    }
}
?>

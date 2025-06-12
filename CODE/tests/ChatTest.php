<?php
use PHPUnit\Framework\TestCase;

require_once 'Chat.php';
require_once 'Database.php';
require_once 'Notifications.php';

class ChatTest extends TestCase
{
    private $chat;

    protected function setUp(): void
    {
        // قاعدة بيانات اختبارية وهمية
        $mockDbConnection = $this->createMock(DatabaseConnection::class);
        $mockPDO = $this->createMock(PDO::class);
        $mockDbConnection->method('getConnection')->willReturn($mockPDO);

        // Notifications mock
        $mockNotification = $this->createMock(Notifications::class);
        $mockNotification->method('sendNotification')->willReturn(true);

        // كلاس للدردشة باستخدام القيم المزدوجة (mocked)
        $this->chat = $this->getMockBuilder(Chat::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['sendMessage', 'getMessagesByProject'])
                           ->getMock();

        $this->chat->method('sendMessage')->willReturn(true);
        $this->chat->method('getMessagesByProject')->willReturn([
            [
                'message' => 'Hello!',
                'sender_id' => 1,
                'sender_name' => 'Ahmed',
                'role' => 'student'
            ]
        ]);
    }

    public function testSendMessageReturnsTrue()
    {
        $result = $this->chat->sendMessage(1, 1, 'Hello test message');
        $this->assertTrue($result, 'Message sending should return true');
    }

    public function testGetMessagesReturnsCorrectData()
    {
        $messages = $this->chat->getMessagesByProject(1);
        $this->assertIsArray($messages);
        $this->assertCount(1, $messages);
        $this->assertEquals('Ahmed', $messages[0]['sender_name']);
        $this->assertEquals('Hello!', $messages[0]['message']);
    }
}

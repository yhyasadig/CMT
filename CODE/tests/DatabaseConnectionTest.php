<?php
use PHPUnit\Framework\TestCase;

require_once 'Database.php'; // تأكد من أن المسار صحيح حسب مكان الملف

class DatabaseConnectionTest extends TestCase
{
    // ✅ اختبار: هل يتم إنشاء الاتصال فعليًا؟
    public function testConnectionIsEstablished()
    {
        $db = new DatabaseConnection();
        $this->assertTrue($db->isConnected(), 'Database should be connected');
    }

    // ✅ اختبار: هل getConnection تُرجع كائن من نوع PDO؟
    public function testGetConnectionReturnsPDO()
    {
        $db = new DatabaseConnection();
        $this->assertInstanceOf(PDO::class, $db->getConnection(), 'getConnection should return PDO instance');
    }

    // ✅ اختبار: هل الاتصال يعمل فعلاً من خلال تنفيذ استعلام بسيط؟
    public function testPDOConnectionIsWorking()
    {
        $db = new DatabaseConnection();
        $pdo = $db->getConnection();

        $stmt = $pdo->query('SELECT 1');
        $result = $stmt->fetchColumn();

        $this->assertEquals(1, $result, 'Connection should execute queries');
    }
}

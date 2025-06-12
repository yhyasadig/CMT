<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Report.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Notifications.php';

class ReportTest extends TestCase
{
    private $mockDb;
    private $mockPdo;
    private $mockStmt;

    protected function setUp(): void
    {
        // إنشاء mock لـ PDO و PDOStatement
        $this->mockDb = $this->createMock(DatabaseConnection::class);
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        // إعداد الاتصال للرجوع بـ PDO المزيف
        $this->mockDb->method('getConnection')->willReturn($this->mockPdo);

        // إعادة تهيئة الـ Singleton يدويًا للاختبار
        $refClass = new ReflectionClass(Report::class);
        $refProp = $refClass->getProperty('instance');
        $refProp->setAccessible(true);
        $refProp->setValue(null); // إعادة التهيئة

        // عمل override على constructor يدويًا (اختبارياً فقط)
        $refConstructor = $refClass->getConstructor();
        $refConstructor->setAccessible(true);
        $report = $refClass->newInstanceWithoutConstructor();
        $refConstructor->invoke($report);

        // تجاوز التنبيه الحقيقي
        $refProp = $refClass->getProperty('instance');
        $refProp->setAccessible(true);
        $refProp->setValue($report);
    }

    public function testSingletonInstance()
    {
        $instance1 = Report::getInstance();
        $instance2 = Report::getInstance();
        $this->assertSame($instance1, $instance2, "Report should be singleton");
    }

    public function testAddReportSuccess()
    {
        $report = Report::getInstance();

        // تجهيز statement
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);
        $this->mockStmt->expects($this->once())->method('execute')->willReturn(true);

        // محاكاة Notifications
        $mockNotif = $this->createMock(Notifications::class);
        $mockNotif->expects($this->once())->method('sendNotification');

        // استبدال الخصائص الخاصة بالقيم mock
        $ref = new ReflectionClass(Report::class);
        $dbProp = $ref->getProperty('db');
        $dbProp->setAccessible(true);
        $dbProp->setValue($report, $this->mockPdo);

        $notifProp = $ref->getProperty('notification');
        $notifProp->setAccessible(true);
        $notifProp->setValue($report, $mockNotif);

        $result = $report->addReport(1, 2, 'student', 'عنوان', 'محتوى', 'file.pdf');
        $this->assertTrue($result);
    }
}

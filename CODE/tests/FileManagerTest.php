<?php
use PHPUnit\Framework\TestCase;

require_once 'FileManager.php';

class FileManagerTest extends TestCase
{
    private $pdoMock;
    private $fileManager;

    protected function setUp(): void
    {
        // إنشاء mock لـ PDO
        $this->pdoMock = $this->createMock(PDO::class);
        $this->fileManager = new FileManager($this->pdoMock);
    }

    public function testUploadTaskFileSuccess()
    {
        $file = [
            'name' => 'test.txt',
            'tmp_name' => __DIR__ . '/test_files/temp_test.txt',
            'error' => UPLOAD_ERR_OK
        ];

        // تجهيز الملف الوهمي
        if (!is_dir(__DIR__ . '/uploads/task_files/')) {
            mkdir(__DIR__ . '/uploads/task_files/', 0777, true);
        }
        file_put_contents($file['tmp_name'], "dummy content");

        $result = $this->fileManager->uploadTaskFile(1, $file, 123);
        $this->assertTrue($result);

        // تنظيف
        unlink(__DIR__ . '/uploads/task_files/test.txt');
        unlink($file['tmp_name']);
    }

    public function testGetFilesByTaskReturnsArray()
    {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->expects($this->once())
                      ->method('fetchAll')
                      ->willReturn([['file_name' => 'test.txt']]);

        $this->pdoMock->expects($this->once())
                      ->method('prepare')
                      ->willReturn($statementMock);

        $statementMock->expects($this->once())->method('execute');
        $statementMock->expects($this->once())->method('bindParam');

        $result = $this->fileManager->getFilesByTask(1);
        $this->assertIsArray($result);
        $this->assertEquals('test.txt', $result[0]['file_name']);
    }

    public function testDeleteFileReturnsTrueOnSuccess()
    {
        // إنشاء ملف وهمي
        $fakeFilePath = __DIR__ . '/uploads/task_files/delete_me.txt';
        if (!is_dir(dirname($fakeFilePath))) {
            mkdir(dirname($fakeFilePath), 0777, true);
        }
        file_put_contents($fakeFilePath, "delete me");

        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('fetch')->willReturn(['file_path' => $fakeFilePath]);

        $this->pdoMock->expects($this->any())
                      ->method('prepare')
                      ->willReturn($statementMock);

        $statementMock->expects($this->any())->method('bindParam');
        $statementMock->expects($this->any())->method('execute');

        $result = $this->fileManager->deleteFile(1);
        $this->assertTrue($result);
    }
}

<?php
use PHPUnit\Framework\TestCase;

require_once 'FileManager.php';

class FileManagerTest extends TestCase
{
    private $pdoMock;
    private $fileManager;

    protected function setUp(): void
    {
        // إنشاء Mock لـ PDO
        $this->pdoMock = $this->createMock(PDO::class);
        $this->fileManager = new FileManager($this->pdoMock);
    }

    public function testUploadTaskFileFailsBecauseOfMoveUpload()
    {
        // ملف عادي وهمي (لن يُقبل من move_uploaded_file)
        $file = [
            'name' => 'test.txt',
            'tmp_name' => __DIR__ . '/test_files/temp_test.txt',
            'error' => UPLOAD_ERR_OK
        ];

        // إنشاء الملف المؤقت
        if (!is_dir(__DIR__ . '/test_files/')) {
            mkdir(__DIR__ . '/test_files/', 0777, true);
        }
        file_put_contents($file['tmp_name'], 'dummy');

        // هنا نتوقع أن الدالة تُفشل عملية النقل لأن move_uploaded_file سترفض الملف
        $result = $this->fileManager->uploadTaskFile(1, $file, 123);

        $this->assertEquals("فشل في تحميل الملف.", $result);

        // تنظيف
        if (file_exists($file['tmp_name'])) unlink($file['tmp_name']);
    }

    public function testGetFilesByTaskReturnsArray()
    {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->expects($this->once())
                      ->method('fetchAll')
                      ->willReturn([['file_name' => 'test.txt']]);

        $statementMock->expects($this->once())->method('execute');
        $statementMock->expects($this->once())->method('bindParam');

        $this->pdoMock->expects($this->once())
                      ->method('prepare')
                      ->willReturn($statementMock);

        $result = $this->fileManager->getFilesByTask(1);
        $this->assertIsArray($result);
        $this->assertEquals('test.txt', $result[0]['file_name']);
    }

    public function testDeleteFileReturnsTrueOnSuccess()
    {
        $fakeFilePath = __DIR__ . '/uploads/task_files/delete_me.txt';
        if (!is_dir(dirname($fakeFilePath))) {
            mkdir(dirname($fakeFilePath), 0777, true);
        }
        file_put_contents($fakeFilePath, "delete me");

        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('fetch')->willReturn(['file_path' => $fakeFilePath]);
        $statementMock->method('execute')->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($statementMock);

        $statementMock->expects($this->any())->method('bindParam');
        $statementMock->expects($this->any())->method('execute');

        $result = $this->fileManager->deleteFile(1);
        $this->assertTrue($result);

        // تنظيف الملف فعليًا إذا لم يُحذف
        if (file_exists($fakeFilePath)) {
            unlink($fakeFilePath);
        }
    }
}

<?php
use PHPUnit\Framework\TestCase;
require_once 'Rating.php';

class RatingTest extends TestCase
{
    private $dbMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    public function testConstructorInitializesValues()
    {
        $rating = new Rating(null, 1, 2, 85);
        $this->assertEquals(1, $rating->getTaskId());
        $this->assertEquals(2, $rating->getUserId());
        $this->assertEquals(85, $rating->getScore());
        $this->assertNotEmpty($rating->getTimestamp());
    }

    public function testSaveToDatabaseInsertsNewRating()
    {
        $this->stmtMock->method('rowCount')->willReturn(0);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $rating = new Rating(null, 1, 2, 90);
        $result = $rating->saveToDatabase($this->dbMock);
        $this->assertTrue($result);
    }

    public function testSaveToDatabaseUpdatesExistingRating()
    {
        $this->stmtMock->expects($this->any())
                       ->method('rowCount')
                       ->willReturn(1); // يعني أن التقييم موجود مسبقًا
        $this->stmtMock->expects($this->any())
                       ->method('execute')
                       ->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $rating = new Rating(null, 1, 2, 95);
        $result = $rating->saveToDatabase($this->dbMock);
        $this->assertTrue($result);
    }

    public function testUpdateRatingSuccess()
    {
        $rating = new Rating(5, 1, 2, 88);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $rating->updateRating($this->dbMock, 99);
        $this->assertTrue($result);
        $this->assertEquals(99, $rating->getScore());
    }

    public function testDeleteRatingSuccess()
    {
        $rating = new Rating(5, 1, 2, 88);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = $rating->deleteRating($this->dbMock);
        $this->assertTrue($result);
    }

    public function testGetRatingsByTaskId()
    {
        $expected = [['score' => 80], ['score' => 90]];
        $this->stmtMock->method('execute');
        $this->stmtMock->method('fetchAll')->willReturn($expected);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = Rating::getRatingsByTaskId($this->dbMock, 1);
        $this->assertEquals($expected, $result);
    }

    public function testGetSupervisorNameByTaskId()
    {
        $this->stmtMock->method('execute');
        $this->stmtMock->method('fetch')->willReturn(['supervisor_name' => 'Ahmed']);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = Rating::getSupervisorNameByTaskId($this->dbMock, 1);
        $this->assertEquals('Ahmed', $result);
    }

    public function testCheckIfRatingExistsTrue()
    {
        $this->stmtMock->method('execute');
        $this->stmtMock->method('rowCount')->willReturn(1);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = Rating::checkIfRatingExists($this->dbMock, 1, 2);
        $this->assertTrue($result);
    }

    public function testCheckIfRatingExistsFalse()
    {
        $this->stmtMock->method('execute');
        $this->stmtMock->method('rowCount')->willReturn(0);
        $this->dbMock->method('prepare')->willReturn($this->stmtMock);

        $result = Rating::checkIfRatingExists($this->dbMock, 1, 2);
        $this->assertFalse($result);
    }
}

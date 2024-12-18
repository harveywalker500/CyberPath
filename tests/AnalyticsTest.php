<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '../php/functions.php'; // Include necessary files

class AnalyticsTest extends TestCase
{
    private $pdo;

    // Setup connection for test cases
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("
            CREATE TABLE userTable (
                userID INTEGER PRIMARY KEY,
                forename TEXT,
                organisationID INTEGER
            );

            CREATE TABLE employeeStatus (
                userID INTEGER,
                isActive INTEGER
            );

            CREATE TABLE storyCompletionLog (
                userID INTEGER,
                durationInSeconds INTEGER
            );

            CREATE TABLE episodeCompletionLog (
                userID INTEGER,
                durationInSeconds INTEGER
            );
        ");

        $this->pdo->exec("INSERT INTO userTable VALUES (1, 'John', 1), (2, 'Jane', 1)");
        $this->pdo->exec("INSERT INTO employeeStatus VALUES (1, 1), (2, 0)");
        $this->pdo->exec("INSERT INTO storyCompletionLog VALUES (1, 120), (1, 150)");
        $this->pdo->exec("INSERT INTO episodeCompletionLog VALUES (1, 300), (2, 180)");
    }

    public function testFetchOverview(): void
    {
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM userTable) AS totalUsers,
                (SELECT COUNT(*) FROM employeeStatus WHERE isActive = 1) AS activeUsers
        ";

        $stmt = $this->pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(2, $result['totalUsers']);
        $this->assertEquals(1, $result['activeUsers']);
    }

    public function testFetchStoryCompletionAverage(): void
    {
        $query = "SELECT AVG(durationInSeconds) AS avgStoryTime FROM storyCompletionLog";
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(135, $result['avgStoryTime']); // (120 + 150) / 2
    }

    public function testFetchEpisodeCompletionAverage(): void
    {
        $query = "SELECT AVG(durationInSeconds) AS avgEpisodeTime FROM episodeCompletionLog";
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(240, $result['avgEpisodeTime']); // (300 + 180) / 2
    }

    protected function tearDown(): void
    {
        $this->pdo = null; // Close connection
    }
}

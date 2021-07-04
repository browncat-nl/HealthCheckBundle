<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\Check;

use Browncat\HealthCheckBundle\Check\DoctrineConnectionHealthCheck;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class DoctrineConnectionHealthCheckTest extends TestCase
{
    public function testSkippedWithoutDoctrine(): void
    {
        $result = new DoctrineConnectionHealthCheck(null);

        $this->assertTrue($result->isSkipped());
    }

    public function testNotSkippedWithDoctrine(): void
    {
        $doctrineMock = $this->createMock(Registry::class);

        $doctrineMock
            ->expects($this->any())
            ->method('getConnections')
            ->willReturn([]);

        $result = new DoctrineConnectionHealthCheck($doctrineMock);

        $this->assertFalse($result->isSkipped());
    }

    public function testSucceededWithoutConnections(): void
    {
        $doctrineMock = $this->createMock(Registry::class);

        $doctrineMock
            ->expects($this->any())
            ->method('getConnections')
            ->willReturn([]);

        $result = new DoctrineConnectionHealthCheck($doctrineMock);

        $this->assertTrue($result->isSucceeded());
    }

    public function testSucceededWithValidConnection(): void
    {
        $doctrineMock   = $this->createMock(Registry::class);
        $connectionMock = $this->createMock(Connection::class);

        $connectionMock
            ->expects($this->any())
            ->method('isConnected')
            ->willReturn(true);

        $doctrineMock
            ->expects($this->any())
            ->method('getConnections')
            ->willReturn([$connectionMock]);

        $result = new DoctrineConnectionHealthCheck($doctrineMock);

        $this->assertTrue($result->isSucceeded());
    }

    public function testFailedWithInvalidConnection(): void
    {
        $doctrineMock   = $this->createMock(Registry::class);
        $connectionMock = $this->createMock(Connection::class);

        $connectionMock
            ->expects($this->any())
            ->method('isConnected')
            ->willReturn(false);

        $doctrineMock
            ->expects($this->any())
            ->method('getConnections')
            ->willReturn([$connectionMock]);

        $result = new DoctrineConnectionHealthCheck($doctrineMock);

        $this->assertFalse($result->isSucceeded());
    }
}

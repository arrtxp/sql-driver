<?php

namespace TestsUnit;

use SqlDriver\Adapter;
use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    protected static Adapter $adapter;

    protected function setUp(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->getMockBuilder(\PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->method('query')->willReturn($stmt);
        $pdo->method('exec')->willReturn(1);
        $pdo->method('quote')->willReturnCallback(function ($value) {
            return "'{$value}'";
        });

        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['pdo'])
            ->getMock();
        $adapter->method('pdo')->willReturn($pdo);

        static::$adapter = $adapter;
    }

    protected static function getAdapter(): Adapter
    {
        return self::$adapter;
    }
}
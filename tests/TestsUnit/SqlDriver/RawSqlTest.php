<?php

namespace TestsUnit\SqlDriver;

use Arrtxp\SqlDriver\RawSql;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;

class RawSqlTest extends Base
{
    #[DataProvider('dataProvider')]
    public function testDelete(string $condition, mixed $value, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, (new RawSql($condition, $value))->toString(self::getAdapter()));
    }

    public static function dataProvider(): array
    {
        return [
            [
                'condition' => "id = 1",
                'value' => null,
                'expectedQuery' => "id = 1",
            ],
            [
                'condition' => "id = 'Jan'",
                'value' => null,
                'expectedQuery' => "id = 'Jan'",
            ],
            [
                'condition' => "id = ?",
                'value' => 1,
                'expectedQuery' => "id = 1",
            ],
            [
                'condition' => "id IN (?)",
                'value' => [1, 2, 3],
                'expectedQuery' => "id IN (1,2,3)",
            ],
            [
                'condition' => "id IN (?) AND name = ?",
                'value' => [[1, 2, 3], 'Jan'],
                'expectedQuery' => "id IN (1,2,3) AND name = 'Jan'",
            ],
            [
                'condition' => "id IN (?) AND name = ? AND surname IS ?",
                'value' => [[1, 2, 3], 'Jan', null],
                'expectedQuery' => "id IN (1,2,3) AND name = 'Jan' AND surname IS NULL",
            ],
        ];
    }
}
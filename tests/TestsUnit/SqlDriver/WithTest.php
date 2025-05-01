<?php

namespace TestsUnit\SqlDriver;

use SqlDriver\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;
use TestsUnit\Structures\User;

#[CoversClass(With::class)]
class WithTest extends Base
{
    #[DataProvider('dataProvider')]
    public function test(callable $with, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $with()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'with' => [
                static fn() => (new With(self::getAdapter(), 'users', 'u'))
                    ->with(
                        alias: 'a',
                        select: (new Select(self::getAdapter(), 'users', 'u', User::class))
                            ->where('id', 2)
                    )
                    ->with(
                        alias: 'b',
                        select: (new Select(self::getAdapter(), 'users', 'u', User::class))
                            ->where('id', 2)
                    )
                ,
                <<<SQL
WITH
`a` as (SELECT `u`.*
FROM `users` `u`
WHERE `u`.`id` = 2),
`b` as (SELECT `u`.*
FROM `users` `u`
WHERE `u`.`id` = 2)

SQL,
            ],
        ];
    }
}
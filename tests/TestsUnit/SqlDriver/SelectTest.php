<?php

namespace TestsUnit\SqlDriver;

use SqlDriver\JoinType;
use SqlDriver\OrderDirection;
use SqlDriver\RawSql;
use SqlDriver\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Structures\User;
use TestsUnit\Models\Users;
use TestsUnit\Base;

class SelectTest extends Base
{
    #[DataProvider('dataProvider')]
    public function test(callable $select, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $select()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'default' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE 1
SQL,
            ],
            'where' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->where('id', 1)
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE `u`.`id` = 1
SQL,
            ],
            'setColumns' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->columns(['id', 'name'])
                    ->where('id', 2)
                ,
                <<<SQL
SELECT `u`.`id`, `u`.`name`
FROM `users` `u`
WHERE `u`.`id` = 2
SQL,
            ],
            'group' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->where('id', 2)
                    ->group('id')
                    ->group('name')
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE `u`.`id` = 2
GROUP BY `u`.`id`, `u`.`name`
SQL,
            ],
            'limit' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->limit(10, 2)
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE 1
LIMIT 10,10
SQL,
            ],
            'order' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->order('id', OrderDirection::ASC)
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE 1
ORDER BY `u`.`id` ASC
SQL,
            ],
            'orderRand' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->order('RAND()')
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE 1
ORDER BY RAND()
SQL,
            ],
            'rawSql' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->where(new RawSql('id IN (?) AND name = ?', [[1,2], 'Jan']))
                    ->limit(10, 2)
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE id IN (1,2) AND name = 'Jan'
LIMIT 10,10
SQL,
            ],
            'join' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->distinct(true)
                    ->columns(['id', 'name'])
                    ->join(
                        (new Users(self::getAdapter()))
                            ->join('u2')
                            ->type(JoinType::LEFT)
                            ->where('id', 1)
                            ->columns(['id', 'name'])
                    )
                    ->limit(10)
                ,
                <<<SQL
SELECT DISTINCT `u`.`id`, `u`.`name`, `u2`.`id`, `u2`.`name`
FROM `users` `u`
LEFT JOIN `users` `u2`
ON `u2`.`id` = 1
WHERE 1
LIMIT 10
SQL,
            ],
        ];
    }
}
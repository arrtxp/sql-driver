<?php

namespace TestsUnit\SqlDriver;

use Arrtxp\SqlDriver\JoinType;
use Arrtxp\SqlDriver\OrderDirection;
use Arrtxp\SqlDriver\RawSql;
use Arrtxp\SqlDriver\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Models\Roles;
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
                    ->columns(
                        [
                            'id',
                            'amount' => new RawSql('SUM(`u`.`amount`)'),
                        ]
                    )
                    ->group('id')
                ,
                <<<SQL
SELECT `u`.`id`, SUM(`u`.`amount`) as `amount`
FROM `users` `u`
WHERE 1
GROUP BY `u`.`id`
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
                    ->where(new RawSql('id IN (?) AND name = ?', [[1, 2], 'Jan']))
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
                        (new Roles(self::getAdapter()))
                            ->join('r')
                            ->where(new RawSql("`u`.`id` = `r`.`id`"))
                            ->columns(['role'])
                    )
                    ->limit(10)
                ,
                <<<SQL
SELECT DISTINCT `u`.`id`, `u`.`name`, `r`.`role`
FROM `users` `u`
INNER JOIN `roles` `r`
ON `u`.`id` = `r`.`id`
WHERE 1
LIMIT 10
SQL,
            ],
            'with' => [
                static fn() => (new Select(self::getAdapter(), 'users', 'u', User::class))
                    ->columns(['id', 'name'])
                    ->with(
                        alias: 'u2',
                        select: (new Users(self::getAdapter()))
                            ->select()
                            ->where('name', 'Jan')
                            ->columns(['id'])
                    )
                    ->where(new RawSql('u.id IN (SELECT `id` FROM `u2`)'))
                ,
                <<<SQL
WITH
`u2` as (SELECT `u`.`id`
FROM `users` `u`
WHERE `u`.`name` = 'Jan')
SELECT `u`.`id`, `u`.`name`
FROM `users` `u`
WHERE u.id IN (SELECT `id` FROM `u2`)
SQL,
            ],
        ];
    }
}
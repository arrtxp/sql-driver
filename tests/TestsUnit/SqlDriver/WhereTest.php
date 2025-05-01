<?php

namespace TestsUnit\SqlDriver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;

#[CoversClass(Where::class)]
class WhereTest extends Base
{
    #[DataProvider('dataProvider')]
    public function test(callable $where, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $where()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'emptyCondition' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                ,
                <<<MYSQL
WHERE 1
MYSQL,
            ],
            'default' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name', 'Jan')
                ,
                <<<MYSQL
WHERE `u`.`name` = 'Jan'
MYSQL,
            ],
            'defaultIn' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('id', [1, 2,])
                ,
                <<<MYSQL
WHERE `u`.`id` IN (1,2)
MYSQL,
            ],
            'in' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('id IN (?)', [1, 2,])
                ,
                <<<MYSQL
WHERE `u`.`id` IN (1,2)
MYSQL,
            ],
            'notIn' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('id NOT IN (?)', [1, 2,])
                ,
                <<<MYSQL
WHERE `u`.`id` NOT IN (1,2)
MYSQL,
            ],
            'like' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name LIKE ?', 'Jan')
                ,
                <<<MYSQL
WHERE `u`.`name` LIKE 'Jan'
MYSQL,
            ],
            'notLike' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name NOT LIKE ?', 'Jan')
                ,
                <<<MYSQL
WHERE `u`.`name` NOT LIKE 'Jan'
MYSQL,
            ],
            'null' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name IS NULL')
                ,
                <<<MYSQL
WHERE `u`.`name` IS NULL
MYSQL,
            ],
            'orNull' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name', 'Jan')
                    ->orWhere('suranme IS NULL')
                ,
                <<<MYSQL
WHERE `u`.`name` = 'Jan' OR `u`.`suranme` IS NULL
MYSQL,
            ],
            'notNull' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name IS NOT NULL')
                ,
                <<<MYSQL
WHERE `u`.`name` IS NOT NULL
MYSQL,
            ],
            'orNotNull' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name', 'Jan')
                    ->orWhere('surname IS NOT NULL')
                ,
                <<<MYSQL
WHERE `u`.`name` = 'Jan' OR `u`.`surname` IS NOT NULL
MYSQL,
            ],
            'regexp' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name REGEXP ?', 'Jan')
                ,
                <<<MYSQL
WHERE `u`.`name` REGEXP 'Jan'
MYSQL,
            ],
            'bettwen' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name BETTWEN ? AND ?', [1, 3])
                ,
                <<<MYSQL
WHERE `u`.`name` BETTWEN 1 AND 3
MYSQL,
            ],
            'bindParam' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name = :name')
                    ->param('name', 'Jan')
                ,
                <<<MYSQL
WHERE `u`.`name` = 'Jan'
MYSQL,
            ],
            'allCombination' => [
                static fn() => (new Where(self::getAdapter(), 'users', 'u'))
                    ->where('name', 'Jan')
                    ->where('surname = ?', 'Kowalski')
                    ->where('((surname != ?', 'Michalski')
                    ->orWhere('surname LIKE ?)', '%Nowak%')
                    ->orWhere('surname NOT LIKE ?)', '%Baran%')
                    ->orWhere('(id', 1)
                    ->where('id NOT IN(?))', [2, 4])
                ,
                <<<MYSQL
WHERE `u`.`name` = 'Jan' AND `u`.`surname` = 'Kowalski' AND ((`u`.`surname` != 'Michalski' OR `u`.`surname` LIKE '%Nowak%') OR `u`.`surname` NOT LIKE '%Baran%') OR (`u`.`id` = 1 AND `u`.`id` NOT IN(2,4))
MYSQL,
            ],
        ];
    }
}
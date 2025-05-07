<?php

namespace TestsUnit\SqlDriver;

use Arrtxp\SqlDriver\Delete;
use Arrtxp\SqlDriver\JoinType;
use Arrtxp\SqlDriver\RawSql;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;
use TestsUnit\Models\Users;

class DeleteTest extends Base
{
    #[DataProvider('dataProvider')]
    public function testDelete(callable $delete, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $delete()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'deleteAll' => [
                static fn() => (new Delete(self::getAdapter(), 'users', 'u'))
                ,
                <<<SQL
DELETE `u` FROM `users` `u`
WHERE 1
SQL,
            ],
            'deleteRow' => [
                static fn() => (new Delete(self::getAdapter(), 'users', 'u'))
                    ->where('id', 2)
                ,
                <<<SQL
DELETE `u` FROM `users` `u`
WHERE `u`.`id` = 2
SQL,
            ],
            'deleteLimit' => [
                static fn() => (new Delete(self::getAdapter(), 'users', 'u'))
                    ->where('id != ?', 2)
                    ->limit(50)
                ,
                <<<SQL
DELETE `u` FROM `users` `u`
WHERE `u`.`id` != 2
LIMIT 50
SQL,
            ],
            'deleteJoin' => [
                static fn() => (new Delete(self::getAdapter(), 'users', 'u'))
                    ->join(
                        (new Users(self::getAdapter()))
                            ->join('u2')
                            ->type(JoinType::LEFT)
                            ->where(new RawSql('`u2`.`id` = `u`.`id`'))
                    )
                    ->where('id', 2)
                ,
                <<<SQL
DELETE `u` FROM `users` `u`
LEFT JOIN `users` `u2`
ON `u2`.`id` = `u`.`id`
WHERE `u`.`id` = 2
SQL,
            ],
        ];
    }
}
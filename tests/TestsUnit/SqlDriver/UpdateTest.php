<?php

namespace TestsUnit\Core;

use SqlDriver\JoinType;
use SqlDriver\RawSql;
use SqlDriver\Select;
use SqlDriver\Update;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;
use TestsUnit\Models\Users;

#[CoversClass(Update::class)]
class UpdateTest extends \TestsUnit\Base
{
    #[DataProvider('dataProvider')]
    public function test(callable $update, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $update()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'set' => [
                static fn() => (new Update(self::getAdapter(), 'users', 'u'))
                    ->set('id', 1)
                    ->set('name', 'Jan')
                    ->set('surname', null)
                    ->set('amount', 13.12)
                    ->where('id', 2)
                ,
                <<<SQL
UPDATE `users` `u`
SET `u`.`id` = 1, `u`.`name` = 'Jan', `u`.`surname` = NULL, `u`.`amount` = 13.12
WHERE `u`.`id` = 2
SQL,
            ],
            'multipleSet' => [
                static fn() => (new Update(self::getAdapter(), 'users', 'u'))
                    ->set(
                        [
                            'id' => 1,
                            'name' => 'Jan',
                            'surname' => null,
                            'amount' => 13.12,
                        ]
                    )
                    ->where('id', 2)
                ,
                <<<SQL
UPDATE `users` `u`
SET `u`.`id` = 1, `u`.`name` = 'Jan', `u`.`surname` = NULL, `u`.`amount` = 13.12
WHERE `u`.`id` = 2
SQL,
            ],
            'updateJoin' => [
                static fn() => (new Update(self::getAdapter(), 'users', 'u'))
                    ->join(
                        (new Users(self::getAdapter()))
                            ->alias('u2')
                            ->join()
                            ->type(JoinType::LEFT)
                            ->where(new RawSql('`u2`.`id` = `u`.`id`'))

                    )
                    ->set('id', 1)
                    ->where('id', 2)
                ,
                <<<SQL
UPDATE `users` `u`
LEFT JOIN `users` `u2`
ON `u2`.`id` = `u`.`id`
SET `u`.`id` = 1
WHERE `u`.`id` = 2
SQL,
            ],
        ];
    }
}
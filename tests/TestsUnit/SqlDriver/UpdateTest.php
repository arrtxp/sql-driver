<?php

namespace TestsUnit\Core;

use Arrtxp\SqlDriver\JoinType;
use Arrtxp\SqlDriver\RawSql;
use Arrtxp\SqlDriver\Update;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
            'limit' => [
                static fn() => (new Update(self::getAdapter(), 'users', 'u'))
                    ->set('surname', null)
                    ->limit(20)
                ,
                <<<SQL
UPDATE `users` `u`
SET `u`.`surname` = NULL
WHERE 1
LIMIT 20
SQL,
            ],
            'rawSql' => [
                static fn() => (new Update(self::getAdapter(), 'users', 'u'))
                    ->set('name', new RawSql("IFNULL(`name`, ?)", 'Jan'))
                    ->where('id', 2)
                ,
                <<<SQL
UPDATE `users` `u`
SET `u`.`name` = (IFNULL(`name`, 'Jan'))
WHERE `u`.`id` = 2
SQL,
            ],
            'arraySet' => [
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
                            ->join('u2')
                            ->type(JoinType::LEFT)
                            ->where(new RawSql('`u2`.`id` = `u`.`id`'))

                    )
                    ->set('name', 'Jan')
                    ->where('id', 2)
                ,
                <<<SQL
UPDATE `users` `u`
LEFT JOIN `users` `u2`
ON `u2`.`id` = `u`.`id`
SET `u`.`name` = 'Jan'
WHERE `u`.`id` = 2
SQL,
            ],
        ];
    }
}
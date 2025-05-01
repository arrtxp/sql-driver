<?php

namespace TestsUnit\SqlDriver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;
use TestsUnit\Models\Users;

#[CoversClass(With::class)]
class ModelTest extends Base
{
    protected static Users $users;

    protected function setUp(): void
    {
        parent::setUp();

        self::$users = new Users(self::getAdapter());
    }

    #[DataProvider('dataProvider')]
    public function test(callable $model, string $expectedQuery): void
    {
        $model();

        $this->assertEquals($expectedQuery, self::$users->getLastQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'insert' => [
                static fn() => static::$users
                    ->insert()
                    ->add(
                        [
                            'name' => 'Jan',
                            'surname' => 'Kowalski',
                            'age' => 24,
                        ]
                    )
                    ->execute()
                ,
                <<<SQL
INSERT INTO `users` (`name`,`surname`,`age`)
VALUES ('Jan','Kowalski',24)
SQL,
            ],
            'select' => [
                static fn() => static::$users
                    ->select()
                    ->where('id', 1)
                    ->getRow()
                ,
                <<<SQL
SELECT `u`.*
FROM `users` `u`
WHERE `u`.`id` = 1
SQL,
            ],
            'update' => [
                static fn() => static::$users
                    ->update()
                    ->where('id', 1)
                    ->set('name', 'Karol')
                    ->set('surname', 'Nowak')
                    ->execute()
                ,
                <<<SQL
UPDATE `users` `u`
SET `u`.`name` = 'Karol', `u`.`surname` = 'Nowak'
WHERE `u`.`id` = 1
SQL,
            ],
            'delete' => [
                static fn() => static::$users
                    ->delete()
                    ->where('id', 1)
                    ->execute()
                ,
                <<<SQL
DELETE `u` FROM `users` `u`
WHERE `u`.`id` = 1
SQL,
            ],
        ];
    }
}
<?php

namespace TestsUnit\SqlDriver;

use SqlDriver\Insert;
use PHPUnit\Framework\Attributes\DataProvider;
use TestsUnit\Base;

class InsertTest extends Base
{
    #[DataProvider('dataProvider')]
    public function test(callable $insert, string $expectedQuery): void
    {
        $this->assertEquals($expectedQuery, $insert()->getQuery());
    }

    public static function dataProvider(): array
    {
        return [
            'insert' => [
                static fn() => (new Insert(self::getAdapter(), 'users', 'u'))
                    ->add(
                        [
                            'id' => 1,
                            'name' => 'Jan',
                            'surname' => 'Kowalski',
                        ]
                    )
                ,
                <<<SQL
INSERT INTO `users` (`id`,`name`,`surname`)
VALUES (1,'Jan','Kowalski')
SQL,
            ],
            'multiInsert' => [
                static fn() => (new Insert(self::getAdapter(), 'users', 'u'))
                    ->add(
                        [
                            'id' => 1,
                            'name' => 'Jan',
                            'surname' => 'Kowalski',
                        ]
                    )
                    ->add(
                        [
                            'id' => 2,
                            'name' => 'Karol',
                            'surname' => 'Major',
                        ]
                    )
                ,
                <<<SQL
INSERT INTO `users` (`id`,`name`,`surname`)
VALUES (1,'Jan','Kowalski'),(2,'Karol','Major')
SQL,
            ],
            'insertIgnore' => [
                static fn() => (new Insert(self::getAdapter(), 'users', 'u'))
                    ->ignore(true)
                    ->add(
                        [
                            'id' => 1,
                            'name' => 'Jan',
                            'surname' => 'Kowalski',
                        ]
                    )
                    ->add(
                        [
                            'id' => 2,
                            'name' => 'Karol',
                            'surname' => 'Major',
                        ]
                    )
                ,
                <<<SQL
INSERT IGNORE INTO `users` (`id`,`name`,`surname`)
VALUES (1,'Jan','Kowalski'),(2,'Karol','Major')
SQL,
            ],
            'insertOnDuplicateKeyUpdate' => [
                static fn() => (new Insert(self::getAdapter(), 'users', 'u'))
                    ->setDuplicateKeyUpdate(
                        [
                            'id',
                            'name',
                            'surname' => 'Aaaa',
                        ]
                    )
                    ->setDuplicateKeyUpdate('age', 12)
                    ->add(
                        [
                            'id' => 1,
                            'name' => 'Jan',
                            'surname' => 'Kowalski',
                        ]
                    )
                    ->add(
                        [
                            'id' => 2,
                            'name' => 'Karol',
                            'surname' => 'Major',
                        ]
                    )
                ,
                <<<SQL
INSERT INTO `users` (`id`,`name`,`surname`)
VALUES (1,'Jan','Kowalski'),(2,'Karol','Major')
ON DUPLICATE KEY UPDATE `u`.`id` = VALUES(`u`.`id`), `u`.`name` = VALUES(`u`.`name`), `u`.`surname` = 'Aaaa', `u`.`age` = 12
SQL,
            ],
        ];
    }
}
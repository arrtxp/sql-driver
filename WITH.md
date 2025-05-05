```php
<?php

/** 
 * WITH
 * `u2` as (SELECT `u`.`id`FROM `users` `u` WHERE `u`.`name` = 'Jan')
 * SELECT `u`.`id`, `u`.`name`
 * FROM `users` `u`
 * WHERE u.id IN (SELECT `id` FROM `u2`)
 */
$model
    ->select()
    ->columns(['id', 'name'])
    ->with(
        alias: 'u2',
        select: (new Users(self::getAdapter()))
            ->select()
            ->where('name', 'Jan')
            ->columns(['id'])
    )
    ->where(new RawSql('u.id IN (SELECT `id` FROM `u2`)'))
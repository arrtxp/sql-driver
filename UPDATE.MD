```php
<?php

/**
 * UPDATE `table` `t` 
 * SET `t`.`name` = 'Jan', `t`.`surname` = 'Kowalski'
 * WHERE 1
 */
$model
    ->update()
    ->set('name', 'Jan')
    ->set('surname', 'Kowalski')
    ->execute();
    
/**
 * UPDATE `table` `t` 
 * SET `t`.`name` = 'Jan'
 * WHERE `t`.`id` = 1
 */
$model
    ->update()
    ->set('name', 'Jan')
    ->where('id', 1)
    ->execute();

/**
 * UPDATE `table` `t` 
 * SET `t`.`name` = IFNULL(`name`, 'Jan')
 * WHERE `t`.`id` = 1
 */
$model
    ->update()
    ->set('name', new RawSql("IFNULL(`t`.`name`, ?)", 'Jan'))
    ->where('id', 1)
    ->execute();

/**
 * UPDATE `table` `t`
 * SET `t`.`id` = 1, `t`.`name` = 'Jan', `t`.`surname` = NULL
 * WHERE `t`.`id` = 2
*/
$model
    ->update()
    ->set(
        [
            'id' => 1,
            'name' => 'Jan',
            'surname' => null,
        ]
    )
    ->where('id', 2)
    ->execute();

/**
 * UPDATE `table` `t`
 * LEFT JOIN `table` `t2`
 * ON `t2`.`id` = `t`.`id`
 * SET `t`.`name` = 'Jan'
 * WHERE `t`.`id` = 2
*/
$model
    ->update()
    ->join(
        $otherModel
            ->join('t2')
            ->type(JoinType::LEFT)
            ->where(new RawSql('`t`.`id` = `t2`.`id`'))
    )
    ->set('name', 'Jan')
    ->where('id', 2)
    ->execute();
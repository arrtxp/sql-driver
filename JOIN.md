```php
<?php

/** 
 * SELECT DISTINCT `t`.`id`, `t`.`name`, `t2`.`role`
 * FROM `table` `t`
 * INNER JOIN `table` `t2`
 * ON `t2`.`id` = `t`.`id`
 * WHERE 1
 * LIMIT 10
 */
$model
    ->select()
    ->columns(['id', 'name'])
    ->join(
        $otherModel
            ->join('u2')
            ->where(new RawSql("`t`.`id` = `t2`.`id`"))
            ->columns(['role'])
    )
    ->limit(10)
    ->getRows(UserRole::class);
    
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
    
/**
 * DELETE `t` FROM `table` `u`
 * INNER JOIN `table` `u2`
 * ON `t`.`id` = `t2`.`id`
 * WHERE `t`.`id` != 2
 */
$model->
    delete()
    ->join(
        $otherModel->
            ->join('t2')
            ->where(new RawSql('`t`.`id` = `t2`.`id`'))
    )
    ->where('id != ?', 2)
    ->execute();

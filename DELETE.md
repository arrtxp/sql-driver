```php
<?php

/**
 * DELETE `t` FROM `table`
 * WHERE 1
 */
$model
    ->delete()
    ->execute();
    
/**
 * DELETE `t` FROM `table`
 * WHERE `t`.`id` IN (1, 2, 3)
 */
$model
    ->delete()
    ->where('id', [1,2,3])
    ->execute();
    
/**
 * DELETE `t` FROM `table`
 * WHERE `t`.`id` IN (1, 2, 3)
 * LIMIT 50
 */
$model
    ->delete()
    ->where('id', [1,2,3])
    ->limit(50)
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
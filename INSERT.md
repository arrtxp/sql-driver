```php
<?php

/**
 * INSERT INTO `table` (`name`)
 * VALUES ('Jan')
 */
$model
    ->insert()
    ->add(
        [
            'name' => 'Jan',
        ]
    )
    ->execute();
    
/**
 * INSERT IGNORE INTO `table` (`name`)
 * VALUES ('Jan')
 */
$model
    ->insert()
    ->ignore(true)
    ->add(
        [
            'name' => 'Jan',
        ]
    )
    ->execute();

/**
 * INSERT INTO `table` (`name`)
 * VALUES ('Jan'),('Mat')
 */
$model
    ->insert()
    ->add(
        [
            'name' => 'Jan',
        ]
    )
    ->add(
        [
            'name' => 'Mat',
        ]
    )
    ->execute();

/**
 * INSERT INTO `table` (`name`)
 * VALUES ('Jan')
 * ON DUPLICATE KEY UPDATE `name` = 'Jan-duplicate'
 */
$model
    ->insert()
    ->setDuplicateKeyUpdate('name', 'Jan-duplicate')
    ->add(
        [
            'name' => 'Jan',
        ]
    )
    ->execute();

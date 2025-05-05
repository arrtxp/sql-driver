```php
<?php

class User {
  public int $id;
  public string $name;
}

class UserStats {
  public int $id;
  public float $amount;
}

class UserRole extends User {
  public string $role;
}

/**
 * SELECT `t`.* FROM `table` `t` 
 * WHERE `t`.`id` != 1
 * ORDER BY `t`.`id` DESC
 * LIMIT 50
 */
$users = $model
    ->select()
    ->limit(50)
    ->order('id', OrderDirection::DESC)
    ->getRows(User::class);
    
foreach($users as $user) {
    
}

/**
 * SELECT `t`.* FROM `table` `t` 
 * WHERE `t`.`id` = 1
 */
$user = $model
    ->select()
    ->where('id', 1)
    ->getRow(User::class);
    
if ($user && $user->id === 1) {
    // bum...
}

/**
 * SELECT `t`.`id`, SUM(`t`.`amount`) as `amount`,
 * FROM `table` `t`
 * WHERE 1
 * GROUP BY `t`.`id`
 */
$stats = $model
    ->select()
    ->columns(
        [
            'id',
            'amount' => new RawSql('SUM(`t`.`amount`)'),
        ]
    )
    ->group('id')
    ->getRows(UserStats::class);

foreach ($stats as $row) {
    echo "id: {$row->id}, amount: {$row->amount}";
}

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
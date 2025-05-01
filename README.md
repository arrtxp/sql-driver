#Requirements
- PHP 8.3
- MySql
- PDO

#Composer
composer require arrtxp/sql-driver

#Usage
```php
<?php

use SqlDriver\Adapter;
use SqlDriver\Model;

$configDatabase = [
  'driver' => 'Pdo',
  'driver_options' => [
      PDO::ATTR_CASE => PDO::CASE_NATURAL,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
      PDO::ATTR_STRINGIFY_FETCHES => false,
      PDO::ATTR_EMULATE_PREPARES => false,
  ],
  'dsn' => 'mysql:dbname=test;host=localhost;charset=utf8mb4',
  'dbname' => 'test',
  'username' => 'test',
  'password' => 'test',
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
],

$adapter = new Adapter($configDatabase);

class User {
  public int $id;
  public string $name;
}

class Users extends Model {
  public string $table = 'users';
  public string $structure = User::class;
}

$modelUser = new Users($adapter);

// insert
// INSERT INTO `users` (`name`) VALUES ('Test')
$userId = $modelUser
  ->insert()
  ->add(
    [
      'name' => 'Test',
    ]
  )
  ->execute();

// get row
// SELECT `u`.* FROM `users` `u` WHERE `u`.`id` = 1
$user = $modelUser
  ->select()
  ->where('id', $userId)
  ->getRow();

// get rows
// SELECT `u`.* FROM `users` `u` WHERE 1
$users = $modelUser
  ->select()
  ->getRows();

// update row
// UPDATE `users` `u` SET `u`.`name` = 'Jan' WHERE `u`.`id` = 1
$modelUser
  ->update()
  ->set('name', 'Jan')
  ->where('id', $userId)
  ->execute();

// delete row
// DELETE `u` FROM `users` `u` WHERE `u`.`id` = 1
$modelUser
  ->delete()
  ->where('id', $userId)
  ->execute();

````

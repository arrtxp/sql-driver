<?php

namespace TestsUnit\Models;

use SqlDriver\Model;
use TestsUnit\Structures\User;

class Users extends Model
{
    public string $table = 'users';
    public string $structure = User::class;
}
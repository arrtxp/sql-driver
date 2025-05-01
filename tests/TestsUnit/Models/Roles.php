<?php

namespace TestsUnit\Models;

use SqlDriver\Model;
use TestsUnit\Structures\Role;

class Roles extends Model
{
    public string $table = 'roles';
    public string $structure = Role::class;
}
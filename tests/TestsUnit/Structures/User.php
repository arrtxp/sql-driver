<?php

namespace TestsUnit\Structures;

class User
{
    public int $id;
    public string $name;
    public string $surname;
    public int $age;

    protected function prepareData(): void
    {
        var_dump(2);
    }
}
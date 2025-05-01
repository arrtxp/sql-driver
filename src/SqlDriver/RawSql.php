<?php

namespace SqlDriver;

class RawSql
{
    public function __construct(
        public string $condition,
        public mixed $value = null
    )
    {
    }
}
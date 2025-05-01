<?php

namespace SqlDriver;

abstract class With extends Where
{
    private array $with = [];

    public function with(string $alias, Select $select): self
    {
        $this->with[$alias] = $select->getQuery();

        return $this;
    }

    protected function getWith(): string
    {
        if (empty($this->with)) {
            return "";
        }

        $with = "";

        foreach ($this->with as $alias => $sql) {
            $with .= $with ? "," : "";
            $with .= PHP_EOL . "`{$alias}` as ({$sql})";
        }

        return "WITH{$with}" . PHP_EOL;
    }

    public function getQuery(): string
    {
        return $this->getWith();
    }
}
<?php

namespace SqlDriver;

/**
 * @property Join[] $join
 */
class Update extends With
{
    private array $set;
    private array $join;

    public function join(Join $join): self
    {
        $this->join[] = $join;

        return $this;
    }

    public function set(string|array $field, null|string|int|float|bool|array $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->set[$field] = $this->adapter->filter($value);
        }

        return $this;
    }

    public function getQuery(): string
    {
        $set = [];
        foreach ($this->set as $field => $value) {
            $set[] = "`$this->alias`.`{$field}` = {$value}";
        }

        $set = implode(', ', $set);

        $join = "";
        if (isset($this->join)) {
            foreach ($this->join as $j) {
                $join .= PHP_EOL . $j->getCondition();
            }
        }

        return <<<SQL
UPDATE `{$this->table}` `{$this->alias}`{$join}
SET {$set}
{$this->getCondition()}
SQL;
    }

    public function execute(): false|int
    {
        return $this->adapter->exec($this->getQuery());
    }
}
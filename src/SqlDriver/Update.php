<?php

namespace Arrtxp\SqlDriver;

/**
 * @property Join[] $join
 */
class Update extends With
{
    private array $set;
    private array $join;
    private int $limit;

    public function join(Join $join): self
    {
        $this->join[] = $join;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function set(string|array $field, null|string|int|float|bool|array|RawSql $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->set[$field] = $value instanceof RawSql ? $value : $this->adapter->filter($value);
        }

        return $this;
    }

    public function getQuery(): string
    {
        $set = "";

        foreach ($this->set as $field => $value) {
            $set .= $set ? ", " : "";

            if ($value instanceof RawSql) {
                $set .= "`$this->alias`.`{$field}` = ({$value->toString($this->adapter)})";
            } else {
                $set .= "`$this->alias`.`{$field}` = {$value}";
            }
        }

        $join = "";
        if (isset($this->join)) {
            foreach ($this->join as $j) {
                $join .= PHP_EOL . $j->getCondition();
            }
        }

        $limit = "";
        if (isset($this->limit)) {
            $limit = PHP_EOL . "LIMIT {$this->limit}";
        }

        return <<<SQL
UPDATE `{$this->table}` `{$this->alias}`{$join}
SET {$set}
{$this->getCondition()}{$limit}
SQL;
    }

    public function execute(): false|int
    {
        return $this->adapter->exec($this->getQuery());
    }
}
<?php

namespace SqlDriver;

class Join extends Where
{
    private JoinType $type = JoinType::INNER;
    private array $columns;

    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): string
    {
        if (!isset($this->columns)) {
            return '';
        }

        $columns = "";

        foreach ($this->columns as $key => $column) {
            $columns .= ", ";

            if ($column === '*') {
                $columns .= "`{$this->alias}`.*";
            } elseif (is_int($key)) {
                $columns .= "`{$this->alias}`.`{$column}`";
            } elseif ($column instanceof RawSql) {
                $columns .= "({$column->toString($this->adapter)}) as `{$key}`";
            } else {
                $columns .= "`{$this->alias}`.`{$column}` as `{$key}`";
            }
        }

        return $columns;
    }

    public function type(JoinType $type): Join
    {
        $this->type = $type;

        return $this;
    }

    public function getCondition(): string
    {
        $condition = str_replace('WHERE', 'ON', parent::getCondition());

        return trim(
            <<<SQL
{$this->type->value} JOIN `{$this->table}` `{$this->alias}`
{$condition}
SQL
        );
    }
}
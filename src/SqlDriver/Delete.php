<?php

namespace SqlDriver;

/**
 * @property Join[] $join
 */
class Delete extends With
{
    private array $join;

    public function join(Join $join): self
    {
        $this->join[] = $join;

        return $this;
    }

    public function getQuery(): string
    {
        $join = "";
        if (isset($this->join)) {
            foreach ($this->join as $j) {
                $join .= PHP_EOL . $j->getCondition();
            }
        }

        return <<<SQL
DELETE `$this->alias` FROM `{$this->table}` `{$this->alias}`{$join}
{$this->getCondition()}
SQL;
    }

    public function execute(): false|int
    {
        return $this->adapter->exec($this->getQuery());
    }
}
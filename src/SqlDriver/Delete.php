<?php

namespace Arrtxp\SqlDriver;

/**
 * @property Join[] $join
 */
class Delete extends With
{
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

    public function getQuery(): string
    {
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
DELETE `$this->alias` FROM `{$this->table}` `{$this->alias}`{$join}
{$this->getCondition()}{$limit}
SQL;
    }

    public function execute(): false|int
    {
        return $this->adapter->exec($this->getQuery());
    }
}
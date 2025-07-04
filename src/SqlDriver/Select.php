<?php

namespace Arrtxp\SqlDriver;

/**
 * @property Join[] $join
 */
class Select extends With
{
    private bool $distinct = false;

    private string $sequence;

    private array $columns;
    private array $join;
    private array $group;
    private array $order;

    public int $limit;
    public int $page;

    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function sequence(string $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function distinct(bool $distinct): self
    {
        $this->distinct = $distinct;

        return $this;
    }

    public function limit(int $limit, ?int $page = null): self
    {
        $this->limit = $limit;

        if ($page) {
            $this->page = $page;
        }

        return $this;
    }

    public function order(string $column, OrderDirection $direction = OrderDirection::ASC): self
    {
        $this->order[] = [$column, $direction->value];

        return $this;
    }

    public function group(string $column): self
    {
        $this->group ??= [];
        $this->group[] = $column;

        return $this;
    }

    public function join(Join $join): self
    {
        $this->join[] = $join;

        return $this;
    }

    public function on(): self
    {
        return $this;
    }

    public function getQuery(): string
    {
        $columns = "`{$this->alias}`.*";

        if (isset($this->columns)) {
            $columns = "";
            foreach ($this->columns as $key => $column) {
                if ($columns) {
                    $columns .= ", ";
                }

                if (is_int($key)) {
                    $columns .= "`{$this->alias}`.`{$column}`";
                } elseif ($column instanceof RawSql) {
                    $columns .= $column->toString($this->adapter) . " as `{$key}`";
                } else {
                    $columns .= "`{$this->alias}`.`{$column}` as `{$key}`";
                }
            }
        }

        $distinct = $this->distinct ? ' DISTINCT' : '';

        $join = "";
        $joinColumns = "";
        if (isset($this->join)) {
            foreach ($this->join as $j) {
                $join .= PHP_EOL . $j->getCondition();
                $joinColumns .= $j->getColumns();
            }
        }

        $limit = "";
        if (isset($this->limit, $this->page)) {
            $limit .= PHP_EOL . "LIMIT " . ($this->limit * ($this->page - 1)) . ",{$this->limit}";
        } elseif (isset($this->limit)) {
            $limit .= PHP_EOL . "LIMIT {$this->limit}";
        }

        $order = "";
        if (isset($this->order)) {
            foreach ($this->order as [$column, $direction]) {
                $order .= $order ? ', ' : '';
                $order .= strtolower($column) === 'rand()' ? $column : "`{$this->alias}`.`{$column}` {$direction}";
            }

            $order = PHP_EOL . "ORDER BY " . $order;
        }

        $group = "";
        if (isset($this->group)) {
            foreach ($this->group as $column) {
                $group .= $group ? ', ' : '';
                $group .= "`{$this->alias}`.`{$column}`";
            }

            $group = PHP_EOL . "GROUP BY " . $group;
        }

        return trim(
            <<<SQL
{$this->getWith()}SELECT{$distinct} {$columns}{$joinColumns}
FROM `{$this->table}` `{$this->alias}`{$join}
{$this->getCondition()}{$group}{$order}{$limit}
SQL
        );
    }

    public function selectCount(?string $column = null): int
    {
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $column = $column ? "`{$column}`" : 1;

        $query = explode('FROM', $this->getQuery());
        $result = $this->adapter->query(
            "SELECT COUNT({$distinct}{$column}) AS `count` FROM{$query[1]}",
            \stdClass::class
        );

        return $result[0]->count;
    }

    /**
     * @template T
     * @param class-string<T> $structure
     * @return T[]
     */
    public function getRows(string $structure = \stdClass::class): array
    {
        $result = $this->adapter->query($this->getQuery(), $structure);

        if ($result && method_exists($result[0], 'prepareData')) {
            foreach ($result as $row) {
                $row->prepareData();
            }
        }

        if (isset($this->sequence)) {
            $result = array_combine(array_column($result, $this->sequence), $result);
        }

        return $result;
    }

    /**
     * @template T
     * @param class-string<T> $structure
     * @return T|false
     */
    public function getRow(string $structure = \stdClass::class)
    {
        return current($this->getRows($structure));
    }
}
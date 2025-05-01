<?php

namespace SqlDriver;

class Insert
{
    private array $values;
    private array $onDuplicateKeyUpdate;
    private bool $ignore;

    public function __construct(
        private readonly Adapter $adapter,
        private readonly string $table,
        private readonly string $alias,
    ) {
    }

    public function ignore(bool $value): self
    {
        $this->ignore = $value;

        return $this;
    }

    public function add(array $values): self
    {
        $this->values ??= [];
        $this->values[] = $values;

        return $this;
    }

    public function setDuplicateKeyUpdate(string|array $field, null|float|int|string|RawSql $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if (is_numeric($k)) {
                    $this->setDuplicateKeyUpdate($v);
                } else {
                    $this->setDuplicateKeyUpdate($k, $v);
                }
            }

            return $this;
        }

        $this->onDuplicateKeyUpdate ??= [];

        if ($value !== null) {
            $this->onDuplicateKeyUpdate[$field] = $value;
        } else {
            $this->onDuplicateKeyUpdate[] = $field;
        }

        return $this;
    }

    public function getQuery(): string
    {
        $ignore = !empty($this->ignore) ? ' IGNORE' : '';

        $current = current($this->values);
        $columns = '`' . implode('`,`', array_keys($current)) . '`';
        $values = array_map(
            fn($values) => array_map(fn($val) => $this->adapter->filter($val), $values),
            $this->values
        );

        $values = '('
            . implode(
                '),(',
                array_map(fn($val) => implode(',', $val), $values)
            ) . ')';

        return <<<SQL
INSERT{$ignore} INTO `{$this->table}` ($columns)
VALUES {$values}{$this->builOnDuplicateKeyUpdate()}
SQL;
    }

    private function builOnDuplicateKeyUpdate(): string
    {
        if (!isset($this->onDuplicateKeyUpdate)) {
            return "";
        }

        $onDuplicateKeyUpdate = '';

        foreach ($this->onDuplicateKeyUpdate as $key => $value) {
            $onDuplicateKeyUpdate .= ($onDuplicateKeyUpdate != '') ? ', ' : '';
            if (is_numeric($key)) {
                $onDuplicateKeyUpdate .= "`{$value}` = VALUES(`{$value}`)";
            } elseif ($value instanceof RawSql) {
                $onDuplicateKeyUpdate .= "`{$key}` = {$value->toString($this->adapter)}";
            } else {
                $onDuplicateKeyUpdate .= "`{$key}` = {$this->adapter->filter($value)}";
            }
        }

        return PHP_EOL . 'ON DUPLICATE KEY UPDATE ' . $onDuplicateKeyUpdate;
    }

    public function execute(): false|int
    {
        if (!isset($this->values)) {
            return false;
        }

        $this->adapter->exec($this->getQuery());

        $countValues = count($this->values);
        if ($countValues === 1) {
            return $this->adapter->pdo()->lastInsertId();
        }

        return $countValues;
    }
}
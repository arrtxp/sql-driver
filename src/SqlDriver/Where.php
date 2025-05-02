<?php

namespace SqlDriver;

abstract class Where
{
    private array $conditions = [];
    private array $parameters = [];

    public function __construct(
        protected readonly Adapter $adapter,
        protected readonly string $table,
        protected readonly string $alias,
    ) {
    }

    public function param(string $name, null|string|int|float|array $value)
    {
        $this->parameters[":{$name}"] = $this->adapter->filter($value);

        return $this;
    }

    public function where(string|RawSql $field, null|string|int|float|array $value = null): self
    {
        $this->conditions[] = ['AND', $field, $value];

        return $this;
    }

    public function orWhere(string $field, null|string|int|float|array $value = null): self
    {
        $this->conditions[] = ['OR', $field, $value];

        return $this;
    }

    protected function getCondition(): string
    {
        if (empty($this->conditions)) {
            return "WHERE 1";
        }

        $condition = "";
        $values = [];
        $isSqlDictionary = function ($field) {
            return !in_array(
                strtolower($field),
                [
                    'all',
                    'not',
                    'and',
                    'or',
                    'like',
                    'in',
                    'null',
                    'regexp',
                    'against',
                    'match',
                    'is',
                    'mode',
                    'boolean',
                    'bettwen',
                ]
            );
        };

        foreach ($this->conditions as $i => [$combination, $field, $value]) {
            if ($i) {
                $condition .= " {$combination} ";
            }

            if ($field instanceof RawSql) {
                $field = $field->toString($this->adapter);
            } else {
                $fields = str_word_count($field, 1, '_1234567890:');
                $fields = array_filter($fields, $isSqlDictionary);
                $replace = [];
                $replaceParams = [];

                foreach ($fields as $_field) {
                    if (isset($this->parameters[$_field])) {
                        $replaceParams["$_field "] = $this->parameters[$_field];

                        continue;
                    }

                    $replace[$_field] = "`{$this->alias}`.`{$_field}`";

                    if (str_replace(['(', ')'], '', $field) === $_field) {
                        if ($value === null) {
                            $replace[$_field] .= ' IS ?';
                        } elseif (is_array($value)) {
                            $replace[$_field] .= ' IN (?)';
                        } else {
                            $replace[$_field] .= ' = ?';
                        }
                    }
                }

                if ($replaceParams) {
                    $field = str_replace(array_keys($replaceParams), array_values($replaceParams), "{$field} ");
                }

                $field = str_replace(array_keys($replace), array_values($replace), $field);
            }

            $condition .= $field;
            $count = substr_count($field, '?');

            if ($count) {
                if (is_array($value) && $count > 1) {
                    foreach ($value as $v) {
                        $values[] = $this->adapter->filter($v);
                    }
                } else {
                    $values[] = $this->adapter->filter($value);
                }
            }
        }

        try {
            return "WHERE " . vsprintf(str_replace("?", "%s", $condition), $values);
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'The arguments array must contain') !== false) {
                throw new \Exception("Parameters are missing for '{$condition}'", $e->getCode(), $e);
            }

            throw $e;
        }
    }

    public function getQuery(): string
    {
        return $this->getCondition();
    }
}
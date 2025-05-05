<?php

namespace SqlDriver;

readonly class RawSql
{
    public function __construct(
        public string $condition,
        public mixed $value = null
    ) {
    }

    public function toString(Adapter $adapter): string
    {
        $count = substr_count($this->condition, '?');
        if (!$count) {
            return $this->condition;
        }

        $values = [];

        if (is_array($this->value) && $count > 1) {
            foreach ($this->value as $v) {
                $values[] = $adapter->filter($v);
            }
        } else {
            $values[] = $adapter->filter($this->value);
        }

        return vsprintf(str_replace('?', "%s", $this->condition), $values);
    }
}
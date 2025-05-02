<?php

namespace SqlDriver;

abstract class Model
{
    public string $table;
    public string $alias;

    public function __construct(
        public readonly Adapter $adapter
    ) {
        if (!isset($this->alias)) {
            $alias = '';
            foreach (explode('_', $this->table) as $name) {
                $alias .= $name[0];
            }

            $this->alias = $alias;
        }
    }

    public function update(?string $alias = null): Update
    {
        return new Update(
            adapter: $this->adapter,
            table: $this->table,
            alias: $alias ?? $this->alias
        );
    }

    public function delete(?string $alias = null): Delete
    {
        return new Delete(
            adapter: $this->adapter,
            table: $this->table,
            alias: $alias ?? $this->alias
        );
    }

    public function insert(?string $alias = null): Insert
    {
        return new Insert(
            adapter: $this->adapter,
            table: $this->table,
            alias: $alias ?? $this->alias
        );
    }

    public function select(?string $alias = null): Select
    {
        return new Select(
            adapter: $this->adapter,
            table: $this->table,
            alias: $alias ?? $this->alias,
        );
    }

    public function join(?string $alias = null): Join
    {
        return new Join(
            adapter: $this->adapter,
            table: $this->table,
            alias: $alias ?? $this->alias,
        );
    }

    public function getTimeExecute(): float
    {
        return $this->adapter->getTimeExecute();
    }

    public function getLastQuery(): ?string
    {
        return $this->adapter->getLastQuery();
    }

    public function disconnect(): void
    {
        $this->adapter->disconnect();
    }
}
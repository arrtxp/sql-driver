<?php

namespace SqlDriver;

abstract class Model
{
    public string $table;
    public string $structure;
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

    public function structure(string $name): self
    {
        $this->structure = $name;

        return $this;
    }

    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function update(): Update
    {
        return new Update(
            adapter: $this->adapter,
            table: $this->table,
            alias: $this->alias
        );
    }

    public function delete(): Delete
    {
        return new Delete(
            adapter: $this->adapter,
            table: $this->table,
            alias: $this->alias
        );
    }

    public function insert(): Insert
    {
        return new Insert(
            adapter: $this->adapter,
            table: $this->table,
            alias: $this->alias
        );
    }

    public function select(): Select
    {
        return new Select(
            adapter: $this->adapter,
            table: $this->table,
            alias: $this->alias,
            structure: $this->structure
        );
    }

    public function join(): Join
    {
        return new Join(
            adapter: $this->adapter,
            table: $this->table,
            alias: $this->alias,
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
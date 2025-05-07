<?php

namespace Arrtxp\SqlDriver;

class Adapter
{
    private array $config;
    private \Pdo $pdo;
    private array $microTime = [
        'start' => 0,
        'end' => 0,
    ];
    private string $lastQuery;

    public function __construct(array $config)
    {
        $this->config = $config['db'];
    }

    public function pdo(): \Pdo
    {
        if (!isset($this->pdo)) {
            $this->pdo = new \Pdo(
                'mysql:host=localhost;dbname=' . $this->config['dbname'],
                $this->config['username'],
                $this->config['password'],
                $this->config['driver_options'],
            );
        }

        return $this->pdo;
    }

    public function filter(mixed $value): int|float|string
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_numeric($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return (int)$value;
        } elseif (is_array($value)) {
            foreach ($value as &$v) {
                $v = $this->filter($v);
            }

            return implode(',', $value);
        }

        return $this->pdo()->quote($value);
    }

    public function exec(string $query): false|int
    {
        $this->lastQuery = $query;

        $this->startMicroTime();
        $result = $this->pdo()->exec($query);
        $this->stopMicroTime();

        return $result;
    }

    /**
     * @template T
     * @param class-string<T> $structure
     * @return T[]
     */
    public function query(string $query, string $structure = \stdClass::class): array
    {
        $this->lastQuery = $query;

        $this->startMicroTime();
        $result = $this->pdo()->query($query)->fetchAll(\PDO::FETCH_CLASS, $structure);
        $this->startMicroTime();

        return $result;
    }

    private function startMicroTime(): void
    {
        $this->microtime['start'] = hrtime(true);
    }

    private function stopMicroTime(): void
    {
        $this->microtime['end'] = hrtime(true);
    }

    public function getTimeExecute(): float
    {
        return ($this->microtime['end'] - $this->microtime['start']) / 1e+6;
    }

    public function getLastQuery(): ?string
    {
        return $this->lastQuery ?? null;
    }

    public function setLastQuery(string $lastQuery): void
    {
        $this->lastQuery = $lastQuery;
    }

    public function disconnect(): void
    {
        unset($this->pdo);
    }
}
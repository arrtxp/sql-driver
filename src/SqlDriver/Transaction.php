<?php

namespace Arrtxp\SqlDriver;

final class Transaction
{
    protected bool $transaction = false;

    public function __construct(protected readonly Adapter $adapter)
    {
    }

    public function begin(): void
    {
        $this->adapter->pdo()->beginTransaction();
        $this->transaction = true;
    }

    public function commit(): void
    {
        $this->adapter->pdo()->commit();
        $this->transaction = false;
    }

    public function rollback(): void
    {
        $this->adapter->pdo()->rollBack();
        $this->transaction = false;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }
}
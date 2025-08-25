<?php
namespace App\Infrastructure\Database;

use PDO;

class UnitOfWork
{
    private PDO $connection;
    private array $new = [];
    private array $dirty = [];
    private array $removed = [];

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function registerNew($entity, callable $callback): void
    {
        $this->new[] = ['entity' => $entity, 'callback' => $callback];
    }

    public function registerDirty($entity, callable $callback): void
    {
        $this->dirty[] = ['entity' => $entity, 'callback' => $callback];
    }

    public function registerRemoved($entity, callable $callback): void
    {
        $this->removed[] = ['entity' => $entity, 'callback' => $callback];
    }

    public function commit(): void
    {
        $this->connection->beginTransaction();

        try {
            foreach ($this->new as $item) {
                $item['callback']($item['entity']);
            }

            foreach ($this->dirty as $item) {
                $item['callback']($item['entity']);
            }

            foreach ($this->removed as $item) {
                $item['callback']($item['entity']);
            }

            $this->connection->commit();

            // clear tracked entities after commit
            $this->new = $this->dirty = $this->removed = [];

        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}

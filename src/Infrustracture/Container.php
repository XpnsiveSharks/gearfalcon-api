<?php
declare(strict_types=1);

namespace App\Infrastructure;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class Container implements ContainerInterface
{
    private array $definitions = [];
    private array $instances = [];

    public function set(string $id, callable $resolver): void
    {
        $this->definitions[$id] = $resolver;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->definitions[$id])) {
            throw new class("No entry found for $id") extends \Exception implements NotFoundExceptionInterface {};
        }

        $this->instances[$id] = $this->definitions[$id]($this);
        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || isset($this->instances[$id]);
    }
}

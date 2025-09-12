<?php

declare(strict_types=1);

namespace App\Domain\Common;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class Entity
{
    protected string $id;
    protected DateTimeImmutable $createdAtUtc;
    protected DateTimeImmutable $lastModifiedAtUtc;
    protected ?DateTimeImmutable $deletedAtUtc = null;

    public function __construct()
    {
        $this->id = $this->setId();
        $this->createdAtUtc = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastModifiedAtUtc = $this->createdAtUtc;
    }

    // ID
    protected function setId(string $id = null): string
    {
        return $id ?? Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    // CreatedAt
    public function getCreatedAtUtc(): DateTimeImmutable
    {
        return $this->createdAtUtc;
    }

    public function setCreatedAt(DateTimeImmutable $date): void
    {
        $this->createdAtUtc = $date;
    }

    // UpdatedAt
    public function getLastModifiedAtUtc(): DateTimeImmutable
    {
        return $this->lastModifiedAtUtc;
    }

    public function setUpdatedAt(DateTimeImmutable $date): void
    {
        $this->lastModifiedAtUtc = $date;
    }

    // DeletedAt
    public function setDeletedAt(?DateTimeImmutable $date): void
    {
        $this->deletedAtUtc = $date;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAtUtc !== null;
    }

    // Touch (update last modified timestamp)
    public function touch(): void
    {
        $this->lastModifiedAtUtc = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}

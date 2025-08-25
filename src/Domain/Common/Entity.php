<?php

declare(strict_types=1);
//**************************************Base Entity***********************************************//
// So basically this abstract class will be the parent of all entities
// this class automatically generates a Unique ID
// Once you create an object out of the class that inherets this, their date and time properties is not changeable
namespace App\Domain\Common;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class Entity
{
    protected readonly string $id;
    protected DateTimeImmutable $createdAtUtc;
    protected DateTimeImmutable $lastModifiedAtUtc;
    protected ?DateTimeImmutable $deletedAtUtc = null; // default null

    public function __construct()
    {
        $this->id = $this->setId();
        $this->createdAtUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastModifiedAtUtc = $this->createdAtUtc;
    }

    // ID
    protected function setId(string $id = null): string
    {
        return $id ?? \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    // CreatedAt
    public function getCreatedAtUtc(): \DateTimeImmutable
    {
        return $this->createdAtUtc;
    }

    public function setCreatedAt(\DateTimeImmutable $date): void
    {
        $this->createdAtUtc = $date;
    }

    // UpdatedAt
    public function getLastModifiedAtUtc(): \DateTimeImmutable
    {
        return $this->lastModifiedAtUtc;
    }

    public function setUpdatedAt(\DateTimeImmutable $date): void
    {
        $this->lastModifiedAtUtc = $date;
    }
    public function setDeletedAt(?DateTimeImmutable $date): void
    {
        $this->deletedAtUtc = $date;
    }
    public function isDeleted(): bool
    {
        return $this->deletedAtUtc !== null;
    }
    // Touch
    public function touch(): void
    {
        $this->lastModifiedAtUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}

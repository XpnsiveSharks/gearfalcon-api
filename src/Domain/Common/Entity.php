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
    // We set this ID to readonly because a readonly property can only be assigned once—this is what makes it immutable.
    protected readonly string $id;
    // we set date to immutable to prevent modification on the current date and time
    protected DateTimeImmutable $createdAtUtc;
    protected DateTimeImmutable $lastModifiedAtUtc;
    public function __construct()
    {
        $this->id = $this->generateId();
        $this->createdAtUtc = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastModifiedAtUtc = $this->createdAtUtc;
    }
    // will generate a unique id
    // probability of duplicating a UUID is close to zero according to the library author
    // so don't be afraid of collision, this won't also throw user facing error if it creates a duplicate id which is rare, it will just generate a new one
    protected function generateId(): string
    {
        return Uuid::uuid4()->toString();
    }
    // i think the rest wuthout comment is self explanatory.......................jk tinamad lng yan
    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAtUtc(): DateTimeImmutable
    {
        return $this->createdAtUtc;
    }

    public function getLastModifiedAtUtc(): DateTimeImmutable
    {
        return $this->lastModifiedAtUtc;
    }
    // this touch function is used so we can track the last time an entity is changed
    // Each time we modify any property of the entity, we should call touch().
    // This ensures lastModifiedAtUtc always reflects the latest change.
    public function touch(): void
    {
        $this->lastModifiedAtUtc = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
    // this equal function will be usefull for checking if 2 entities are the same 
    // example in the repository if we're saving a new entity we need to check if that generated Id already exist in the existing data from the db
    public function equals(Entity $entity): bool
    {
        return $entity->getId() === $this->id;
    }
}

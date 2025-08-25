<?php

declare(strict_types=1);
//*********************************************************Value-object*********************************************************//
namespace App\Domain\User\ValueObjects;

final class ContactInfo
{
    private ?string $phone;

    public function __construct(?string $phone)
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function withPhone(string $phone): self
    {
        return new self($phone);
    }
    // --- Hydration ---
    public static function fromArray(array $data): self
    {
        return new self($data['phone'] ?? null);
    }

    // --- Dehydration ---
    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
        ];
    }
}

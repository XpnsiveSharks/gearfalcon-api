<?php
declare(strict_types=1);
//*********************************************************Value-object*********************************************************//
namespace App\Domain\User\ValueObjects;

final class ContactInfo
{
    private string $email;
    private ?string $phone;

    public function __construct(string $email, ?string $phone = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address.");
        }

        $this->email = $email;
        $this->phone = $phone; // optional, can validate format if needed
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function withEmail(string $email): self
    {
        return new self($email, $this->phone);
    }

    public function withPhone(?string $phone): self
    {
        return new self($this->email, $phone);
    }
}

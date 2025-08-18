<?php
declare(strict_types=1);
//*********************************************************Value-object*********************************************************//
namespace App\Domain\User\ValueObjects;

final class Credentials
{
    private string $passwordHash;

    public function __construct(string $plainPassword)
    {
        $this->passwordHash = $this->hashPassword($plainPassword);
    }

    private function hashPassword(string $password): string
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException("Password must be at least 8 characters.");
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    public function withPassword(string $newPassword): self
    {
        return new self($newPassword);
    }
}

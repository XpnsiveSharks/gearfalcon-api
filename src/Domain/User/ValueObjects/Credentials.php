<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final class Credentials
{
    private string $email;
    private string $passwordHash;

    // Constructor for new users (plain password)
    public function __construct(string $email, string $plainPassword)
    {
        $this->email = $this->validateEmail($email);
        $this->passwordHash = $this->hashPassword($plainPassword);
    }

    private function validateEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format.");
        }
        return strtolower($email);
    }

    private function hashPassword(string $password): string
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException("Password must be at least 8 characters.");
        }
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    public function withPassword(string $newPassword): self
    {
        return new self($this->email, $newPassword);
    }

    // --- Hydration from database (hashed password already stored) ---
    public static function fromHashed(string $email, string $hashedPassword): self
    {
        $instance = new self($email, $hashedPassword);
        $instance->email = strtolower($email);
        $instance->passwordHash = $hashedPassword; // set directly without hashing
        return $instance;
    }

    public static function fromArray(array $data): self
    {
        $instance = new self($data['email'], $data['password_hash']);
        $instance->email = strtolower($data['email']);
        $instance->passwordHash = $data['password_hash']; // set directly without hashing
        return $instance;
    }

    // --- Dehydration to array (for DB or API response) ---
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
        ];
    }
}

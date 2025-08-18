<?php
declare(strict_types=1);

namespace App\Domain\User\Entities;
//*********************************************************Entity*********************************************************//
final class Profile
{
    private string $firstName;
    private string $lastName;
    private ?string $middleName;
    private ?string $avatarUrl; // image example --> $avatarUrl = "https://bucket-name.s3.amazonaws.com/user123.jpg"; (specialized storage not inside db)(CDN for iamge delivery)

    public function __construct(
        string $firstName,
        string $lastName,
        ?string $middleName = null,
        ?string $avatarUrl = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
        $this->avatarUrl = $avatarUrl;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function withFirstName(string $firstName): self
    {
        $clone = clone $this;
        $clone->firstName = $firstName;
        return $clone;
    }

    public function withLastName(string $lastName): self
    {
        $clone = clone $this;
        $clone->lastName = $lastName;
        return $clone;
    }

    public function withMiddleName(?string $middleName): self
    {
        $clone = clone $this;
        $clone->middleName = $middleName;
        return $clone;
    }

    public function withAvatarUrl(?string $avatarUrl): self
    {
        $clone = clone $this;
        $clone->avatarUrl = $avatarUrl;
        return $clone;
    }
}

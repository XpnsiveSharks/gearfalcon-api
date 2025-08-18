<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final class Address
{
    private string $houseNumber; // e.g., "123", "Unit 5B"
    private string $street;      // Street name
    private string $barangay;    // Barangay
    private string $city;        // City or Municipality
    private string $province;    // Province
    private ?string $region;     // Region, optional
    private string $postalCode;  // Postal code

    public function __construct(
        string $houseNumber,
        string $street,
        string $barangay,
        string $city,
        string $province,
        string $postalCode,
        ?string $region = null
    ) {
        $this->houseNumber = $houseNumber;
        $this->street = $street;
        $this->barangay = $barangay;
        $this->city = $city;
        $this->province = $province;
        $this->region = $region;
        $this->postalCode = $postalCode;
    }

    public function houseNumber(): string
    {
        return $this->houseNumber;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function barangay(): string
    {
        return $this->barangay;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function province(): string
    {
        return $this->province;
    }

    public function region(): ?string
    {
        return $this->region;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function equals(Address $address): bool
    {
        return $this->houseNumber === $address->houseNumber
            && $this->street === $address->street
            && $this->barangay === $address->barangay
            && $this->city === $address->city
            && $this->province === $address->province
            && $this->region === $address->region
            && $this->postalCode === $address->postalCode;
    }
}

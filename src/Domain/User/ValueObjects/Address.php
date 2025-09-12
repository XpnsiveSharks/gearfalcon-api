<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

/**
 * Value Object: Address
 *
 * Represents an immutable postal address for a User.
 * Provides methods for equality checks and safe conversion
 * between arrays and object instances (hydration/dehydration).
 */
final class Address
{
    private ?string $houseNumber; // e.g., "123", "Unit 5B"
    private ?string $street;      // Street name
    private string $barangay;     // Barangay
    private string $city;         // City or Municipality
    private string $province;     // Province
    private ?string $region;      // Region, optional
    private ?string $postalCode;  // Postal code (4 digits)

    public function __construct(
        ?string $houseNumber,
        ?string $street,
        string $barangay,
        string $city,
        string $province,
        string $region,
        ?string $postalCode = null
    ) {
        // Barangay, City, Province should not be empty
        if (trim($barangay) === '') {
            throw new \InvalidArgumentException("Barangay is required and cannot be empty.");
        }
        if (trim($city) === '') {
            throw new \InvalidArgumentException("City is required and cannot be empty.");
        }
        if (trim($province) === '') {
            throw new \InvalidArgumentException("Province is required and cannot be empty.");
        }
        // Validate postal code only if provided
        if ($postalCode !== null && $postalCode !== '' && !preg_match('/^\d{4}$/', $postalCode)) {
            throw new \InvalidArgumentException("Invalid postal code format. Must be 4 digits.");
        }

        $validRegions = [
            'NCR',
            'CAR',
            'Region I',
            'Region II',
            'Region III',
            'Region IV-A',
            'Region IV-B',
            'Region V',
            'Region VI',
            'Region VII',
            'Region VIII',
            'Region IX',
            'Region X',
            'Region XI',
            'Region XII',
            'Region XIII',
            'BARMM'
        ];

        // Postal code check (must be 4 digits, cannot be empty)
        if ($postalCode === null || trim($postalCode) === '') {
            throw new \InvalidArgumentException("Postal code is required.");
        }
        if (!preg_match('/^\d{4}$/', $postalCode)) {
            throw new \InvalidArgumentException("Invalid postal code format. Must be 4 digits.");
        }

        if ($region !== null && $region !== '' && !in_array($region, $validRegions, true)) {
            throw new \InvalidArgumentException("Invalid region: {$region}");
        }

        $this->houseNumber = $houseNumber;
        $this->street = $street;
        $this->barangay = $barangay;
        $this->city = $city;
        $this->province = $province;
        $this->region = $region;
        $this->postalCode = $postalCode;
    }

    // --- Getters ---
    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }
    public function getStreet(): ?string
    {
        return $this->street;
    }
    public function getBarangay(): string
    {
        return $this->barangay;
    }
    public function getCity(): string
    {
        return $this->city;
    }
    public function getProvince(): string
    {
        return $this->province;
    }
    public function getRegion(): ?string
    {
        return $this->region;
    }
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    // --- Equality check ---
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

    // --- Hydration ---
    public static function fromArray(array $data): self
    {        
        return new self(
            $data['house_number'] ?? null,
            $data['street'] ?? null,
            $data['barangay'] ?? '',
            $data['city'] ?? '',
            $data['province'] ?? '',
            $data['region'] ?? null,
            $data['postal_code'] ?? null
        );
    }

    // --- Dehydration ---
    public function toArray(): array
    {
        return [
            'house_number' => $this->houseNumber,
            'street'       => $this->street,
            'barangay'     => $this->barangay,
            'city'         => $this->city,
            'province'     => $this->province,
            'region'       => $this->region,
            'postal_code'  => $this->postalCode,
        ];
    }
}

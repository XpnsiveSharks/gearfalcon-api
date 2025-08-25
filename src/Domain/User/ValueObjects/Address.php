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
    private string $barangay;    // Barangay
    private string $city;        // City or Municipality
    private string $province;    // Province
    private string $region;     // Region, optional
    private ?string $postalCode;  // Postal code

    /**
     * @param string      $houseNumber House/Unit number (e.g., "123", "Unit 5B")
     * @param string      $street      Street name
     * @param string      $barangay    Barangay
     * @param string      $city        City or Municipality
     * @param string      $province    Province
     * @param string      $postalCode  Postal code
     * @param string|null $region      Region (optional)
     */

    public function __construct(
        ?string $houseNumber,
        ?string $street,
        string $barangay,
        string $city,
        string $province,
        ?string $postalCode  = null,
        string $region
    ) {
        if (!preg_match('/^\d{4}$/', $postalCode)) {
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

        if ($region !== null && !in_array($region, $validRegions, true)) {
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

    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    public function getStreet(): string
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

    public function GetPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Compares this Address to another Address for equality.
     *
     * @param Address $address
     * @return bool
     */

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

    /**
     * Creates an Address from an associative array (hydration).
     *
     * Expected keys:
     * - houseNumber
     * - street
     * - barangay
     * - city
     * - province
     * - postalCode
     * - region (optional)
     *
     * @param array $data
     * @return self
     * fromArray → frontend (JSON request) → backend (hydrate object for DB or domain logic).
     */

    public static function fromArray(array $data): self
    {
        return new self(
            $data['houseNumber']?? '',
            $data['street']?? '',
            $data['barangay']?? '',
            $data['city']?? '',
            $data['province']?? '',
            $data['postalCode']?? '',
            $data['region']?? '',
        );
    }

    /**
     * Converts this Address into an associative array (dehydration).
     *
     * @return array<string, string|null>
     * toArray → backend → frontend (API response).
     */

    public function toArray(): array
    {
        return [
            'houseNumber' => $this->houseNumber,
            'street' => $this->street,
            'barangay' => $this->barangay,
            'city' => $this->city,
            'province' => $this->province,
            'region' => $this->region,
            'postalCode' => $this->postalCode,
        ];
    }
}

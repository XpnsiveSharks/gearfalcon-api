<?php
// src/Infrastructure/Repositories/UsersRepository.php
namespace App\Infrastructure\Repositories;

use PDO;
use App\Domain\User\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UsersRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(\app\domain\User\User $user): void
    {
    $sql = "INSERT INTO users (
        role, is_active, first_name, last_name, middle_name, avatar_url, phone, email, password_hash,
        house_number, street, barangay, city, province, region, postal_code, created_at, updated_at, deleted_at
    ) VALUES (
        :role, :is_active, :first_name, :last_name, :middle_name, :avatar_url, :phone, :email, :password_hash,
        :house_number, :street, :barangay, :city, :province, :region, :postal_code, :created_at, :updated_at, :deleted_at
    )";

    $stmt = $this->db->prepare($sql); // <-- FIXED LINE
    $stmt->execute([
        ':role' => $user->getRole(),
        ':is_active' => $user->isActive(),
        ':first_name' => $user->getFirstName(),
        ':last_name' => $user->getLastName(),
        ':middle_name' => $user->getMiddleName(),
        ':avatar_url' => $user->getAvatarUrl(),
        ':phone' => $user->getPhone(),
        ':email' => $user->getEmail(),
        ':password_hash' => $user->getPasswordHash(),
        ':house_number' => $user->getHouseNumber(),
        ':street' => $user->getStreet(),
        ':barangay' => $user->getBarangay(),
        ':city' => $user->getCity(),
        ':province' => $user->getProvince(),
        ':region' => $user->getRegion(),
        ':postal_code' => $user->getPostalCode(),
        ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ':updated_at' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ':deleted_at' => $user->getDeletedAt() ? $user->getDeletedAt()->format('Y-m-d H:i:s') : null,
    ]);
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? User::fromArray($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at IS NULL");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? User::fromArray($data) : null;
    }

    public function update(User $user): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users SET
                role = :role,
                first_name = :first_name,
                last_name = :last_name,
                middle_name = :middle_name,
                avatar_url = :avatar_url,
                phone = :phone,
                email = :email,
                password_hash = :password_hash,
                house_number = :house_number,
                street = :street,
                barangay = :barangay,
                city = :city,
                province = :province,
                region = :region,
                postal_code = :postal_code,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute($user->toArray());
    }

    public function delete(string $id): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    private function mapRowToUser(array $data): User
    {
        return User::fromArray($data);
    }
}

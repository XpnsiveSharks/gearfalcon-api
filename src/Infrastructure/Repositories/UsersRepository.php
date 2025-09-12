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

    /**
     * Create a new user.
     *
     * @param \App\Domain\User\User $user
     * @return void
     */

    public function create(User $user): bool
    {
        // Use the aggregate's toArray() to get DB-ready fields
        $data = $user->toArray();

        $sql = "INSERT INTO users (
            role, is_active, first_name, last_name, middle_name, avatar_url, phone, email, password_hash,
            house_number, street, barangay, city, province, region, postal_code, created_at, updated_at, deleted_at
        ) VALUES (
            :role, :is_active, :first_name, :last_name, :middle_name, :avatar_url, :phone, :email, :password_hash,
            :house_number, :street, :barangay, :city, :province, :region, :postal_code, :created_at, :updated_at, :deleted_at
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role' => $data['role'] ?? 'Customer',
            ':is_active' => ($data['is_active'] ?? true) ? 1 : 0,
            ':first_name' => $data['first_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':avatar_url' => $data['avatar_url'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':password_hash' => $data['password_hash'] ?? null,
            ':house_number' => $data['house_number'] ?? null,
            ':street' => $data['street'] ?? null,
            ':barangay' => $data['barangay'] ?? null,
            ':city' => $data['city'] ?? null,
            ':province' => $data['province'] ?? null,
            ':region' => $data['region'] ?? null,
            ':postal_code' => $data['postal_code'] ?? null,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            ':updated_at' => $data['updated_at'] ?? null,
            ':deleted_at' => $data['deleted_at'] ?? null,
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

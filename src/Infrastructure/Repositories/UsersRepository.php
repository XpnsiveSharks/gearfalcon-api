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

    public function create(User $user): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users 
                (id, role, first_name, last_name, middle_name, avatar_url,
                 phone, email, password_hash,
                 house_number, street, barangay, city, province, region, postal_code)
            VALUES 
                (:id, :role, :first_name, :last_name, :middle_name, :avatar_url,
                 :phone, :email, :password_hash,
                 :house_number, :street, :barangay, :city, :province, :region, :postal_code)
        ");

        return $stmt->execute($user->toArray());
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

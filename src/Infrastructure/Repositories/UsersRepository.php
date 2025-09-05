<?php
// src/Infrastructure/Repositories/UsersRepository.php
namespace App\Infrastructure\Repositories;

use PDO;
use App\Domain\User\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UsersRepository implements UserRepositoryInterface
{
    private PDO $db;
     
    // construnctor accepts a database connection
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Creates `INSERT` query for users table and returns true is the execution is successful
    // Accepts a user object
    // And returns a boolean
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

        // `$user->toArray()` a function from Domain/User/Users.php that converts User object to associative array 
        return $stmt->execute($user->toArray());
    }

    // This function Creates a `SELECT` query with condition (if the id from parameter is equal to the id in the users table AND deleted is null) 
    // will return a User Object/Entity
    public function findById(string $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // If data is true is will execute this `User::fromArray($data)`->convert array of data to entity/object if it is false return null
        // `User::fromArray($data)` - a function from Domain/User/Users.php
        // `null` - the else part of the ternary operator
        return $data ? User::fromArray($data) : null; // ternary operator just like if else 
    }

    // This function Creates a `SELECT` query with condition (if the email from parameter is equal to the email in the users table AND deleted is null) 
    // if the condition is true it will return that User
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND deleted_at IS NULL");
        $stmt->execute(['email' => $email]); 
        $data = $stmt->fetch(PDO::FETCH_ASSOC); // Fetches an associative array (fetches all users related table columns) and store it to `data`

        // If data is true is will execute this `User::fromArray($data)`->convert array of data to entity/object if it is false return null
        // `User::fromArray($data)` - a function from Domain/User/Users.php
        // `null` - the else part of the ternary operator
        return $data ? User::fromArray($data) : null; // ternary operator just like if else 
    }

    // Creates `UPDATE` query for users with a condition (if id from parameter(User) is equal to id from users table) table and returns true is the execution is successful
    // Accepts a user object
    // Returns a boolean
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

        // `$user->toArray()` a function from Domain/User/Users.php that converts User object to associative array 
        return $stmt->execute($user->toArray());
    }

    // this function uses `soft deletes` you don't totally delete the data we're just updating it's `deleted_at` property to the current timestamp
    // Creates an `UPDATE` query that sets the `deleted_at` property to current timestamp and has a condition (if id from parameter is equal to id from the users table)
    // returns a boolean
    public function delete(string $id): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

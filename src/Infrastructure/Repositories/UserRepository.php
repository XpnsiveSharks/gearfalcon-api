<?php 
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\User;

/**
 * Class UserRepository
 *
 * Repository for handling User model operations.
 * Extends the base Repository to reuse generic CRUD methods
 * and adds custom queries specific to the User domain.
 */
class UserRepository extends Repository
{
    /**
     * UserRepository constructor.
     *
     * @param User $model The User Eloquent model instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find users by role (admin, customer, technician).
     *
     * @param string $role
     * @return \Illuminate\Support\Collection
     */
    public function findByRole(string $role)
    {
        return $this->model->where('role', $role)->get();
    }
}


// $userRepo = new UserRepository(new User());

// // Find a user
// $user = $userRepo->findById('uuid-123');

// // Find by email
// $user = $userRepo->findByEmail('test@example.com');

// // Create user
// $newUser = $userRepo->create([
//     'id' => uniqid(),
//     'name' => 'John Doe',
//     'email' => 'john@example.com',
//     'password' => password_hash('secret', PASSWORD_BCRYPT),
//     'role' => 'customer',
//     'phone' => '09123456789'
// ]);

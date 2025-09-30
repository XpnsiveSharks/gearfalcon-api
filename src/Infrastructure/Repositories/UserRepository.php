<?php 
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\User;

use Ramsey\Uuid\Uuid;
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

    public function create(array $data): User
    {
        if (empty($data['id'])) {
            $data['id'] = Uuid::uuid4()->toString();
        }
        return parent::create($data);
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

    /**
     * Delete users that are not verified and whose verification window expired.
     * A user is considered expired if:
     *  - is_verified = false
     *  - verification_code_expires_at IS NOT NULL AND < NOW() - intervalMinutes
     */
    public function deleteExpiredUnverifiedUsers(int $intervalMinutes = 5): int
    {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$intervalMinutes} minutes"));

        // Use query builder to perform a single delete
        return $this->model
            ->where('is_verified', false)
            ->whereNotNull('verification_code_expires_at')
            ->where('verification_code_expires_at', '<', $threshold)
            ->delete();
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

<?php
namespace App\Domain\User\Repositories;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user.
     */
    public function create(User $user): bool;

    /**
     * Find a user by ID.
     */
    public function findById(string $id): ?User;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update an existing user.
     */
    public function update(User $user): bool;

    /**
     * Delete a user by ID.
     */
    public function delete(string $id): bool;
}

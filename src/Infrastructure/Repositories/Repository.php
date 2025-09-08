<?php
namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Repository
 *
 * High-level abstraction for interacting with Eloquent models.
 * This base Repository provides common CRUD (Create, Read, Update, Delete)
 * operations that can be reused by all repositories in the application.
 *
 * 🔹 Responsibilities:
 * - Encapsulates common database operations.
 * - Provides a consistent API for working with models.
 * - Promotes DRY by avoiding repeating CRUD logic in each repository.
 *
 * 🔹 Usage:
 * Extend this class for each specific repository (e.g. UserRepository, CustomerRepository).
 * Inject the corresponding Eloquent model into the constructor, and you can
 * immediately use all base methods (`findById`, `findAll`, `create`, `update`, `delete`).
 *
 * Example:
 * ```php
 * class UserRepository extends Repository {
 *     public function __construct(User $user) {
 *         parent::__construct($user);
 *     }
 *
 *     public function findByEmail(string $email): ?User {
 *         return $this->model->where('email', $email)->first();
 *     }
 * }
 * ```
 *
 * This way, all repositories share the same base functionality while still
 * allowing custom query methods for each model.
 */
abstract class Repository
{
    protected Model $model;

    /**
     * Repository constructor.
     *
     * @param Model $model The Eloquent model instance tied to this repository.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find a record by its primary key.
     *
     * @param mixed $id
     * @return Model|null
     */
    public function findById($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Retrieve all records.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll(): \Illuminate\Support\Collection
    {
        return $this->model->all();
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by ID.
     *
     * @param mixed $id
     * @param array $data
     * @return Model|null
     */
    public function update($id, array $data): ?Model
    {
        $record = $this->findById($id);
        if (!$record) return null;

        $record->update($data);
        return $record;
    }

    /**
     * Delete a record by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($id): bool
    {
        $record = $this->findById($id);
        return $record ? $record->delete() : false;
    }
}

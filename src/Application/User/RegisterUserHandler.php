<?php
namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Database\UnitOfWork;

final class RegisterUserHandler
{
    private UserRepositoryInterface $usersRepo;
    private UnitOfWork $uow;

    public function __construct(UserRepositoryInterface $usersRepo, UnitOfWork $uow)
    {
        $this->usersRepo = $usersRepo;
        $this->uow = $uow;
    }

    public function handle(User $user): void
    {
        // Register new user in UnitOfWork
        $this->uow->registerNew($user, function(User $u) {
            $this->usersRepo->create($u);
        });

        // If there are other entities related to the user, you can register them too
        // $this->uow->registerNew($profile, fn($p) => $this->profilesRepo->create($p));

        // Commit all at once (transaction)
        $this->uow->commit();
    }
}

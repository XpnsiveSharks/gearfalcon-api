<?php

namespace App\Presentation\Http\Controllers;

use App\Application\User\RegisterUserHandler;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Credentials;
use App\Domain\User\User;

class UserController
{
    private RegisterUserHandler $registerUserHandler;

    public function __construct(RegisterUserHandler $registerUserHandler)
    {
        $this->registerUserHandler = $registerUserHandler;
    }

    public function index()
    {
        return "Hello from UserController";
    }

    public function show(string $id)
    {
        return "Showing user with id: $id";
    }

    public function register(array $data): void
    {
        // --- Hydrate the User aggregate directly from array ---
        $user = User::fromArray($data);

        // --- Call the handler to persist ---
        $this->registerUserHandler->handle($user);

        // --- Return the saved user as array (dehydration) ---
        echo json_encode($user->toArray());
    }
}

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


    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['message' => 'Email and password are required']);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $credentials = Credentials::fromHashed($email, $passwordHash);

        $user = new User(
            'Customer',
            null, // profile
            null, // contact info
            $credentials,
            null  // address
        );

        $this->registerUserHandler->handle($user);

        echo json_encode([
            'success' => true,
            'email' => $credentials->getEmail()
        ]);
    }
}

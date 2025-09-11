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

    // Basic validation
    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['message' => 'Email and password are required']);
        return;
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Build value objects (fill with actual data or defaults)
    $profile = new \App\Domain\User\Entities\Profile(
        '', // first_name
        '', // last_name
        '', // middle_name
        ''  // avatar_url
    );
    $contactInfo = new \App\Domain\User\ValueObjects\ContactInfo(
        '', // phone
        $email
    );
    $credentials = \App\Domain\User\ValueObjects\Credentials::fromHashed(
        $email,
        $passwordHash
    );
    $address = new \App\Domain\User\ValueObjects\Address(
        '', '', '', '', '', '', '', '' // house_number, street, barangay, city, province, region, postal_code
    );

    // Create User aggregate
    $user = new \App\Domain\User\User(
        'Customer', // role
        $profile,
        $contactInfo,
        $credentials,
        $address
    );

    // Save user using handler
    $this->registerUserHandler->handle($user);

    echo json_encode(['success' => true]);
    }
}

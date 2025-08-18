<?php
namespace App\Presentation\Http\Controllers;

class UserController {
    public function index() {
        return "Hello from UserController";
    }

    public function show(string $id) {

        return "Showing user with id: $id";
    }

    public function store() {
        return "Storing a new user";
    }
}

<?php

namespace Database\DataAccess\Interfaces;

use Models\User;

interface UserDAO {
    public function create(User $user, string $password): bool;
    public function getById(int $user_id): ?User;
    public function getByEmail(string $email): ?User;
    public function getHashedPasswordById(int $user_id): ?string;
    public function updateEmailConfirmedAt(int $user_id): bool;
}

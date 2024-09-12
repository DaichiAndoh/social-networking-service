<?php

namespace Database\DataAccess\Interfaces;

use Models\TempUser;

interface TempUserDAO {
    public function create(TempUser $user): bool;
    public function getBySignature(string $signature): ?TempUser;
    public function deleteTempUserById(int $temp_user_id): bool;
}

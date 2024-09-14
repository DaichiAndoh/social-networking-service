<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\UserDAO;
use Database\DatabaseManager;
use Models\User;

class UserDAOImpl implements UserDAO {
    public function create(User $user, string $password): bool {
        if ($user->getUserId() !== null) throw new \Exception("Cannot create a user with an existing ID. user_id: " . $user->getUserId());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "ssss",
            [
                $user->getName(),
                $user->getUsername(),
                $user->getEmail(),
                password_hash($password, PASSWORD_DEFAULT) // store the hashed password
            ]
        );

        if (!$result) return false;

        $user->setUserId($mysqli->insert_id);

        return true;
    }

    private function getRawById(int $user_id): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM users WHERE user_id = ?";

        $result = $mysqli->prepareAndFetchAll($query, "i", [$user_id])[0] ?? null;

        if ($result === null) return null;

        return $result;
    }

    private function getRawByEmail(string $email): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM users WHERE email = ?";

        $result = $mysqli->prepareAndFetchAll($query, "s", [$email])[0] ?? null;

        if ($result === null) return null;

        return $result;
    }

    private function getRawByUsername(string $username): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM users WHERE username = ?";

        $result = $mysqli->prepareAndFetchAll($query, "s", [$username])[0] ?? null;

        if ($result === null) return null;

        return $result;
    }

    private function rawDataToUser(array $rawData): User {
        return new User(
            name: $rawData["name"],
            username: $rawData["username"],
            email: $rawData["email"],
            created_at: $rawData["created_at"],
            updated_at: $rawData["updated_at"],
            user_id: $rawData["user_id"],
            profile_text: $rawData["profile_text"],
            profile_image_hash: $rawData["profile_image_hash"],
            email_confirmed_at: $rawData["email_confirmed_at"],
        );
    }

    public function getById(int $user_id): ?User {
        $userRaw = $this->getRawById($user_id);
        if($userRaw === null) return null;

        return $this->rawDataToUser($userRaw);
    }

    public function getByEmail(string $email): ?User {
        $userRaw = $this->getRawByEmail($email);
        if($userRaw === null) return null;

        return $this->rawDataToUser($userRaw);
    }

    public function getByUsername(string $username): ?User {
        $userRaw = $this->getRawByUsername($username);
        if($userRaw === null) return null;

        return $this->rawDataToUser($userRaw);
    }

    public function getHashedPasswordById(int $user_id): ?string {
        return $this->getRawById($user_id)["password"] ?? null;
    }

    public function updateEmailConfirmedAt(int $user_id): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "UPDATE users SET email_confirmed_at = NOW() WHERE user_id = ?";

        $result = $mysqli->prepareAndExecute($query, "i", [$user_id]);

        return $result;
    }

    public function updatePassword(int $user_id, string $password): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "UPDATE users SET password = ? WHERE user_id = ?";

        $result = $mysqli->prepareAndExecute(
            $query,
            "si",
            [
                password_hash($password, PASSWORD_DEFAULT),
                $user_id
            ]
        );

        return $result;
    }
}

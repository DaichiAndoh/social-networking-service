<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\TempUserDAO;
use Database\DatabaseManager;
use Models\TempUser;

class TempUserDAOImpl implements TempUserDAO {
    public function create(TempUser $tempUser): bool {
        if ($tempUser->getTempUserId() !== null) throw new \Exception("Cannot create a temp_user with an existing ID. temp_user_id: " . $tempUser->getTempUserId());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO temp_users (user_id, signature, type) VALUES (?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dss",
            [
                $tempUser->getUserId(),
                $tempUser->getSignature(),
                $tempUser->getType(),
            ]
        );

        if (!$result) return false;

        $tempUser->setTempUserId($mysqli->insert_id);

        return true;
    }

    public function deleteTempUserById(int $temp_user_id): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "DELETE FROM temp_users WHERE temp_user_id = ?";

        $result = $mysqli->prepareAndExecute($query, "i", [$temp_user_id]);

        return $result;
    }

    public function getBySignature(string $signature): ?TempUser {
        $userRaw = $this->getRawBySignature($signature, $type);
        if($userRaw === null) return null;

        return $this->rawDataToTempUser($userRaw);
    }

    private function getRawBySignature(string $signature): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM temp_users WHERE signature = ?";

        $result = $mysqli->prepareAndFetchAll($query, "s", [$signature])[0] ?? null;

        return $result;
    }

    private function rawDataToTempUser(array $rawData): TempUser {
        return new TempUser(
            temp_user_id: $rawData["temp_user_id"],
            user_id: $rawData["user_id"],
            signature: $rawData["signature"],
            type: $rawData["type"],
            created_at: $rawData["created_at"],
        );
    }
}

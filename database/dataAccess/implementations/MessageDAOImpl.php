<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\MessageDAO;
use Database\DatabaseManager;
use Models\Message;

class MessageDAOImpl implements MessageDAO {
    public function create(Message $message): bool {
        if ($message->getMessageId() !== null) throw new \Exception("このMessageデータを作成することはできません。: " . $message->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO messages (from_user_id, to_user_id, content) VALUES (?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dds",
            [
                $message->getFromUserId(),
                $message->getToUserId(),
                $message->getContent(),
            ],
        );

        if (!$result) return false;

        $message->setMessageId($mysqli->insert_id);

        return true;
    }

    public function getChatUsers(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT u.user_id, u.name, u.username, u.profile_image_hash FROM messages m " .
            "INNER JOIN users u ON u.user_id = m.from_user_id OR u.user_id = m.to_user_id " .
            "WHERE m.from_user_id = ? " .
            "OR m.to_user_id = ? " .
            "GROUP BY u.username " .
            "HAVING user_id <> ? " .
            "ORDER BY MAX(m.created_at) DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiiii", [$user_id, $user_id, $user_id, $limit, $offset]);

        return $result ?? [];
    }

    public function getChatMessages(int $user_id, int $chat_user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT from_user_id, content FROM messages " .
            "WHERE from_user_id = ? " .
            "OR from_user_id = ? " .
            "OR to_user_id = ? " .
            "OR to_user_id = ? " .
            "ORDER BY created_at DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll(
            $query,
            "iiiiii",
            [$user_id, $chat_user_id, $user_id, $chat_user_id, $limit, $offset],
        );

        return $result ?? [];
    }
}

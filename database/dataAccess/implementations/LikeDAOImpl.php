<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\LikeDAO;
use Database\DatabaseManager;
use Models\Like;

class LikeDAOImpl implements LikeDAO {
    public function like(Like $like): bool {
        if ($like->getLikeId() !== null) throw new \Exception("このLikeデータを作成することはできません。: " . $like->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dd",
            [
                $like->getUserId(),
                $like->getPostId(),
            ]
        );

        if (!$result) return false;

        $like->setLikeId($mysqli->insert_id);

        return true;
    }

    public function unlike(int $user_id, int $post_id): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";

        $result = $mysqli->prepareAndExecute($query, "dd", [$user_id, $post_id]);

        return $result;
    }

    public function exists(int $user_id, int $post_id): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM likes WHERE user_id = ? AND post_id = ? LIMIT 1";

        $result = $mysqli->prepareAndFetchAll($query, "dd", [$user_id, $post_id]);

        return count($result) > 0;
    }
}

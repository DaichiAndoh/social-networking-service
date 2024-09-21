<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Models\Post;

class PostDAOImpl implements PostDAO {
    public function create(Post $post): bool {
        if ($post->getPostId() !== null) throw new \Exception("このPostデータを作成することはできません。: " . $post->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO posts (user_id, content, status, image_hash, scheduled_at) VALUES (?, ?, ?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dssss",
            [
                $post->getUserId(),
                $post->getContent(),
                $post->getStatus(),
                $post->getImageHash(),
                $post->getScheduledAt(),
            ]
        );

        if (!$result) return false;

        $post->setPostId($mysqli->insert_id);

        return true;
    }
}

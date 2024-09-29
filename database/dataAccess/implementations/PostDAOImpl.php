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

    public function getPost(int $post_id): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, u.name, u.username, u.profile_image_hash " .
            "FROM posts p INNER JOIN users u ON p.user_id = u.user_id " .
            "WHERE p.post_id = ?";

        $result = $mysqli->prepareAndFetchAll($query, "i", [$post_id]) ?? null;

        return $result ? $result[0] : null;
    }

    public function getReplies(int $post_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, u.name, u.username, u.profile_image_hash " .
            "FROM posts p INNER JOIN users u ON p.user_id = u.user_id " .
            "WHERE p.reply_to_id = ? " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iii", [$post_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getTrendTimelinePosts(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, u.name, u.username, u.profile_image_hash " .
            "FROM posts p INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN follows f ON u.user_id = f.followee_id " .
            "WHERE p.status = 'POSTED' " .
            "AND (f.follower_id = ? OR p.user_id = ?) " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiii", [$user_id, $user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getFollowTimelinePosts(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, u.name, u.username, u.profile_image_hash " .
            "FROM posts p INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN follows f ON u.user_id = f.followee_id " .
            "WHERE p.status = 'POSTED' " .
            "AND (f.follower_id = ? OR p.user_id = ?) " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiii", [$user_id, $user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }
}
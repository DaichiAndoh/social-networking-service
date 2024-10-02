<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Models\Post;

class PostDAOImpl implements PostDAO {
    public function create(Post $post): bool {
        if ($post->getPostId() !== null) throw new \Exception("このPostデータを作成することはできません。: " . $post->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO posts (user_id, reply_to_id, content, status, image_hash, scheduled_at) VALUES (?, ?, ?, ?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "ddssss",
            [
                $post->getUserId(),
                $post->getReplyToId(),
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

    public function getPost(int $post_id, int $authenticated_user_id): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, " .
            "IFNULL(rc.reply_count, 0) AS reply_count, " .
            "IFNULL(lc.like_count, 0) AS like_count, " .
            "CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked, " .
            "u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN (SELECT reply_to_id, COUNT(*) AS reply_count FROM posts WHERE reply_to_id = ? GROUP BY reply_to_id) AS rc ON p.post_id = rc.reply_to_id " .
            "LEFT JOIN (SELECT post_id, COUNT(*) AS like_count FROM likes WHERE post_id = ? GROUP BY post_id) AS lc ON p.post_id = lc.post_id " .
            "LEFT JOIN (SELECT post_id FROM likes WHERE post_id = ? AND user_id = ? GROUP BY post_id) AS l ON p.post_id = l.post_id " .
            "WHERE p.post_id = ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiiii", [$post_id, $post_id, $post_id, $authenticated_user_id, $post_id]) ?? null;

        return $result ? $result[0] : null;
    }

    public function getReplies(int $post_id, int $authenticated_user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, " .
            "IFNULL(rc.reply_count, 0) AS reply_count, " .
            "IFNULL(lc.like_count, 0) AS like_count, " .
            "CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked, " .
            "u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN (SELECT reply_to_id, COUNT(*) AS reply_count FROM posts GROUP BY reply_to_id) AS rc ON p.post_id = rc.reply_to_id " .
            "LEFT JOIN (SELECT post_id, COUNT(*) AS like_count FROM likes GROUP BY post_id) AS lc ON p.post_id = lc.post_id " .
            "LEFT JOIN (SELECT post_id FROM likes WHERE user_id = ? GROUP BY post_id) AS l ON p.post_id = l.post_id " .
            "WHERE p.reply_to_id = ? " .
            "GROUP BY p.post_id, rc.reply_count, lc.like_count " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiii", [$authenticated_user_id, $post_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getTrendTimelinePosts(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, " .
            "IFNULL(rc.reply_count, 0) AS reply_count, " .
            "IFNULL(lc.like_count, 0) AS like_count, " .
            "CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked, " .
            "u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN follows f ON u.user_id = f.followee_id " .
            "LEFT JOIN (SELECT reply_to_id, COUNT(*) AS reply_count FROM posts WHERE reply_to_id IS NOT NULL GROUP BY reply_to_id) AS rc ON p.post_id = rc.reply_to_id " .
            "LEFT JOIN (SELECT post_id, COUNT(*) AS like_count FROM likes GROUP BY post_id) AS lc ON p.post_id = lc.post_id " .
            "LEFT JOIN (SELECT post_id FROM likes WHERE user_id = ? GROUP BY post_id) AS l ON p.post_id = l.post_id " .
            "WHERE p.status = 'POSTED' " .
            "AND p.reply_to_id IS NULL " .
            "AND (f.follower_id = ? OR p.user_id = ?) " .
            "GROUP BY p.post_id, rc.reply_count, lc.like_count " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiiii", [$user_id, $user_id, $user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getFollowTimelinePosts(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, " .
            "IFNULL(rc.reply_count, 0) AS reply_count, " .
            "IFNULL(lc.like_count, 0) AS like_count, " .
            "CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked, " .
            "u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN follows f ON u.user_id = f.followee_id " .
            "LEFT JOIN (SELECT reply_to_id, COUNT(*) AS reply_count FROM posts WHERE reply_to_id IS NOT NULL GROUP BY reply_to_id) AS rc ON p.post_id = rc.reply_to_id " .
            "LEFT JOIN (SELECT post_id, COUNT(*) AS like_count FROM likes GROUP BY post_id) AS lc ON p.post_id = lc.post_id " .
            "LEFT JOIN (SELECT post_id FROM likes WHERE user_id = ? GROUP BY post_id) AS l ON p.post_id = l.post_id " .
            "WHERE p.status = 'POSTED' " .
            "AND p.reply_to_id IS NULL " .
            "AND (f.follower_id = ? OR p.user_id = ?) " .
            "GROUP BY p.post_id, rc.reply_count, lc.like_count " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iiiii", [$user_id, $user_id, $user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getUserPosts(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, COUNT(r.reply_to_id) AS reply_count, u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN posts r ON p.post_id = r.reply_to_id " .
            "WHERE p.status = 'POSTED' " .
            "AND p.user_id = ? " .
            "AND p.reply_to_id IS NULL " .
            "GROUP BY p.post_id " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iii", [$user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getUserReplies(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, COUNT(r.reply_to_id) AS reply_count, u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN posts r ON p.post_id = r.reply_to_id " .
            "WHERE p.status = 'POSTED' " .
            "AND p.user_id = ? " .
            "AND p.reply_to_id IS NOT NULL " .
            "GROUP BY p.post_id " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iii", [$user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }

    public function getUserLikes(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT p.post_id, p.content, p.image_hash, p.updated_at, COUNT(r.reply_to_id) AS reply_count, u.name, u.username, u.profile_image_hash " .
            "FROM posts p " .
            "INNER JOIN users u ON p.user_id = u.user_id " .
            "LEFT JOIN posts r ON p.post_id = r.reply_to_id " .
            "WHERE p.status = 'POSTED' " .
            "AND p.user_id = ? " .
            "AND p.reply_to_id IS NULL " .
            "GROUP BY p.post_id " .
            "ORDER BY p.post_id DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iii", [$user_id, $limit, $offset]) ?? null;

        return $result ?? [];
    }
}

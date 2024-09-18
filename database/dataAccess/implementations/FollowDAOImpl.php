<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\FollowDAO;
use Database\DatabaseManager;
use Models\Follow;

class FollowDAOImpl implements FollowDAO {
    public function follow(Follow $follow): bool {
        if ($follow->getFollowId() !== null) throw new \Exception("このFollowデータを作成することはできません。: " . $follow->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO follows (follower_id, followee_id) VALUES (?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dd",
            [
                $follow->getFollowerId(),
                $follow->getFolloweeId(),
            ]
        );

        if (!$result) return false;

        $follow->setFollowId($mysqli->insert_id);

        return true;
    }

    public function unfollow(Follow $follow): bool {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "DELETE FROM follows WHERE follower_id = ? AND followee_id = ?";

        $result = $mysqli->prepareAndExecute($query, "dd", [$follow->getFollowerId(), $follow->getFolloweeId()]);

        return $result;
    }

    public function getFollowerCount(int $user_id): int {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT COUNT(*) count FROM follows WHERE followee_id = ?";

        $result = $mysqli->prepareAndFetchAll($query, "d", [$user_id]) ?? null;

        if ($result === null || !isset($result[0]["count"])) {
            return 0;
        }
        return (int)$result[0]["count"];
    }

    public function getFolloweeCount(int $user_id): int {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT COUNT(*) count FROM follows WHERE follower_id = ?";

        $result = $mysqli->prepareAndFetchAll($query, "d", [$user_id]) ?? null;

        if ($result === null || !isset($result[0]["count"])) {
            return 0;
        }
        return (int)$result[0]["count"];
    }

    public function getFollowers(int $user_id, int $limit, int $offset): array {}
    public function getFollowees(int $user_id, int $limit, int $offset): array {}
}

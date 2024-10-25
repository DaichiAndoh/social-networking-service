<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class LikeInitSeeder extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string $tableName = "likes";

    // TODO: tableColumns配列の割り当て
    protected array $tableColumns = [
        [
            "data_type" => "int",
            "column_name" => "user_id",
        ],
        [
            "data_type" => "int",
            "column_name" => "post_id",
        ],
    ];

    public function createRowData(): array {
        // TODO: createRowData()メソッドの実装
        $faker = Factory::create();

        $likes = [];

        $influencerIds = self::getAllTestInfluencerIds();
        for ($i = 0; $i < count($influencerIds); $i++) {
            // インフルエンサー > インフルエンサーのポストいいね
            $postIds = self::getTestInfluencerPostIds($influencerIds[$i], INIT_LIKE_TO_INFLUENCER_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $likes[] = [$influencerIds[$i], $postIds[$j]];
            }

            // インフルエンサー > 一般ユーザーのポストいいね
            $postIds = self::getTestUserPostIds($influencerIds[$i], INIT_LIKE_TO_USER_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $likes[] = [$influencerIds[$i], $postIds[$j]];
            }
        }

        $userIds = self::getAllTestUserIds();
        for ($i = 0; $i < count($userIds); $i++) {
            // 一般ユーザー > インフルエンサーのポストいいね
            $postIds = self::getTestInfluencerPostIds($userIds[$i], INIT_LIKE_TO_INFLUENCER_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $likes[] = [$userIds[$i], $postIds[$j]];
            }

            // 一般ユーザー > 一般ユーザーのポストいいね
            $postIds = self::getTestUserPostIds($userIds[$i], INIT_LIKE_TO_USER_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $likes[] = [$userIds[$i], $postIds[$j]];
            }
        }

        return $likes;
    }

    private function getAllTestInfluencerIds(): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT user_id FROM users WHERE email LIKE 'influencer%@example.com' AND type = 'INFLUENCER'";

        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $influencerIds = array_map("intval", array_column($rows, "user_id"));
            return $influencerIds;
        }
        return [];
    }

    private function getTestInfluencerPostIds(int $notUserId, int $limit): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT p.post_id post_id FROM posts p INNER JOIN users u ON p.user_id = u.user_id WHERE u.user_id != ? AND u.email LIKE 'influencer%@example.com' AND u.type = 'INFLUENCER' ORDER BY RAND() LIMIT ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $notUserId, $limit);

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $postIds = array_map("intval", array_column($rows, "post_id"));
            return $postIds;
        }
        return [];
    }

    private function getAllTestUserIds(): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT user_id FROM users WHERE email LIKE 'user%@example.com' AND type = 'USER'";

        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $userIds = array_map("intval", array_column($rows, "user_id"));
            return $userIds;
        }
        return [];
    }

    private function getTestUserPostIds(int $notUserId, int $limit): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT p.post_id post_id FROM posts p INNER JOIN users u ON p.user_id = u.user_id WHERE u.user_id != ? AND u.email LIKE 'user%@example.com' AND u.type = 'USER' ORDER BY RAND() LIMIT ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $notUserId, $limit);

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $postIds = array_map("intval", array_column($rows, "post_id"));
            return $postIds;
        }
        return [];
    }
}

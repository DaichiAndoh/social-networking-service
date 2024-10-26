<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;
use Helpers\DateOperator;
use Models\Post;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class UserReplyBatchSeeder extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string $tableName = "posts";

    // TODO: tableColumns配列の割り当て
    protected array $tableColumns = [
        [
            "data_type" => "int",
            "column_name" => "user_id",
        ],
        [
            "data_type" => "int",
            "column_name" => "reply_to_id",
        ],
        [
            "data_type" => "string",
            "column_name" => "content",
        ],
        [
            "data_type" => "string",
            "column_name" => "status",
        ],
    ];

    public function createRowData(): array {
        // TODO: createRowData()メソッドの実装
        $faker = Factory::create();

        $posts = [];

        $index = array_search(DateOperator::getCurrentHour(), BATCH_HOURS);
        if ($index === false) $index = 0;

        $limit = ceil(INIT_USER_COUNT / count(BATCH_HOURS));
        $offset = $index * $limit;
        $userIds = self::getProtUserIds($limit, $offset);

        for ($i = 0; $i < count($userIds); $i++) {
            // インフルエンサーのポストへのリプライ
            $postIds = self::getProtInfluencerPostIds(BATCH_USER_REPLY_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $posts[] = [
                    $userIds[$i],
                    $postIds[$j],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
                ];
            }

            // 一般ユーザーのポストへのリプライ
            $postIds = self::getProtUserPostIds($userIds[$i], BATCH_USER_REPLY_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $posts[] = [
                    $userIds[$i],
                    $postIds[$j],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
                ];
            }
        }

        return $posts;
    }

    private function getProtUserIds(int $limit, int $offset): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT user_id FROM users WHERE email LIKE 'user%@example.com' AND type != 'INFLUENCER' ORDER BY users.user_id LIMIT ? OFFSET ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $postIds = array_map("intval", array_column($rows, "user_id"));
            return $postIds;
        }
        return [];
    }

    private function getProtInfluencerPostIds(int $limit): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT p.post_id post_id FROM posts p INNER JOIN users u ON p.user_id = u.user_id WHERE u.email LIKE 'influencer%@example.com' AND u.type = 'INFLUENCER' ORDER BY RAND() LIMIT ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $limit);

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $postIds = array_map("intval", array_column($rows, "post_id"));
            return $postIds;
        }
        return [];
    }

    private function getProtUserPostIds(int $notUserId, int $limit): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT p.post_id post_id FROM posts p INNER JOIN users u ON p.user_id = u.user_id WHERE u.user_id != ? AND u.email LIKE 'user%@example.com' AND u.type != 'INFLUENCER' ORDER BY RAND() LIMIT ?";

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

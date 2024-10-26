<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;
use Models\Post;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class UserReplyInitSeeder extends AbstractSeeder {
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
        $userIds = self::getAllTestUserIds();

        for ($i = 0; $i < count($userIds); $i++) {
            // インフルエンサーのポストへのリプライ
            $replyCount = rand(INIT_USER_REPLY_MIN_COUNT, INIT_USER_REPLY_MAX_COUNT);
            $postIds = self::getTestInfluencerPostIds($replyCount);
            for ($j = 0; $j < count($postIds); $j++) {
                $posts[] = [
                    $userIds[$i],
                    $postIds[$j],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
                ];
            }

            // 一般ユーザーのポストへのリプライ
            $replyCount = rand(INIT_USER_REPLY_MIN_COUNT, INIT_USER_REPLY_MAX_COUNT);
            $postIds = self::getTestUserPostIds($userIds[$i], $replyCount);
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

    private function getTestInfluencerPostIds(int $limit): array {
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

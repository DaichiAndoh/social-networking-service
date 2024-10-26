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
        [
            "data_type" => "string",
            "column_name" => "created_at",
        ],
        [
            "data_type" => "string",
            "column_name" => "updated_at",
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
        $userIds = self::getTestUserIds($limit, $offset);

        for ($i = 0; $i < count($userIds); $i++) {
            $postIds = self::getTestUserPostIds($userIds[$i], BATCH_USER_REPLY_COUNT);
            for ($j = 0; $j < count($postIds); $j++) {
                $posts[] = [
                    $userIds[$i],
                    $postIds[$j],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
                    DateOperator::formatDateTime(DateOperator::getCurrentDateTime()),
                    DateOperator::formatDateTime(DateOperator::getCurrentDateTime()),
                ];
            }
        }

        return $posts;
    }

    private function getTestUserIds(int $limit, int $offset): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT user_id FROM users WHERE email LIKE 'user%@example.com' AND type = 'USER' ORDER BY users.user_id LIMIT ? OFFSET ?";

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

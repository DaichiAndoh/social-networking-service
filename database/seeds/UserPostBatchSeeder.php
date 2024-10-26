<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;
use Helpers\DateOperator;
use Models\Post;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class UserPostBatchSeeder extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string $tableName = "posts";

    // TODO: tableColumns配列の割り当て
    protected array $tableColumns = [
        [
            "data_type" => "int",
            "column_name" => "user_id",
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
        $userIds = self::getTestUserIds($limit, $offset);

        for ($i = 0; $i < count($userIds); $i++) {
            for ($j = 0; $j < BATCH_USER_POST_COUNT; $j++) {
                $posts[] = [
                    $userIds[$i],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
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
}

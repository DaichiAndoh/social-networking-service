<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;
use Models\Post;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class UserPostInitSeeder extends AbstractSeeder {
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
        $userIds = self::getAllTestUserIds();

        for ($i = 0; $i < count($userIds); $i++) {
            $postCount = rand(INIT_USER_POST_MIN_COUNT, INIT_USER_POST_MAX_COUNT);
            for ($j = 0; $j < $postCount; $j++) {
                $posts[] = [
                    $userIds[$i],
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
}

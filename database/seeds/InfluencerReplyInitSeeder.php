<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;
use Models\Post;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class InfluencerReplyInitSeeder extends AbstractSeeder {
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
        $influencerIds = self::getAllTestInfluencerIds();

        for ($i = 0; $i < count($influencerIds); $i++) {
            $replyCount = rand(INIT_INFLUENCER_REPLY_MIN_COUNT, INIT_INFLUENCER_REPLY_MAX_COUNT);
            $postIds = self::getTestInfluencerPostIds($influencerIds[$i], $replyCount);
            for ($j = 0; $j < count($postIds); $j++) {
                $posts[] = [
                    $influencerIds[$i],
                    $postIds[$j],
                    $faker->text(Post::$maxLens["content"]),
                    "POSTED",
                ];
            }
        }

        return $posts;
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
}

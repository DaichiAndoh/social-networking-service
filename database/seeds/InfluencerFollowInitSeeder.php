<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Database\MySQLWrapper;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class InfluencerFollowInitSeeder extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string $tableName = "follows";

    // TODO: tableColumns配列の割り当て
    protected array $tableColumns = [
        [
            "data_type" => "int",
            "column_name" => "follower_id",
        ],
        [
            "data_type" => "int",
            "column_name" => "followee_id",
        ],
    ];

    public function createRowData(): array {
        // TODO: createRowData()メソッドの実装
        $follows = [];
        $influencerIds = self::getAllProtInfluencerIds();

        for ($i = 0; $i < count($influencerIds); $i++) {
            $followCount = rand(INIT_INFLUENCER_FOLLOW_MIN_COUNT, INIT_INFLUENCER_FOLLOW_MAX_COUNT);
            $followUserIds = self::getProtInfluencerIds($influencerIds[$i], $followCount);
            for ($j = 0; $j < count($followUserIds); $j++) {
                $follows[] = [$influencerIds[$i], $followUserIds[$j]];
            }
        }

        return $follows;
    }

    private function getAllProtInfluencerIds(): array {
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

    private function getProtInfluencerIds(int $notUserId, int $limit): array {
        $mysqli = new MySQLWrapper();

        $query = "SELECT user_id FROM users WHERE user_id != ? AND email LIKE 'influencer%@example.com' AND type = 'INFLUENCER' ORDER BY RAND() LIMIT ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $notUserId, $limit);

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $influencerIds = array_map("intval", array_column($rows, "user_id"));
            return $influencerIds;
        }
        return [];
    }
}

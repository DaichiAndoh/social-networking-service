<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;
use Helpers\DateOperator;
use Models\User;

require_once(sprintf("%s/../../constants/seed_constants.php", __DIR__));

class InfluencerInitSeeder extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string $tableName = "users";

    // TODO: tableColumns配列の割り当て
    protected array $tableColumns = [
        [
            "data_type" => "string",
            "column_name" => "name",
        ],
        [
            "data_type" => "string",
            "column_name" => "username",
        ],
        [
            "data_type" => "string",
            "column_name" => "email",
        ],
        [
            "data_type" => "string",
            "column_name" => "password",
        ],
        [
            "data_type" => "string",
            "column_name" => "profile_text",
        ],
        [
            "data_type" => "string",
            "column_name" => "type",
        ],
        [
            "data_type" => "string",
            "column_name" => "email_confirmed_at",
        ],
    ];

    public function createRowData(): array {
        // TODO: createRowData()メソッドの実装
        $faker = Factory::create();

        $influencers = [];

        for ($i = 1; $i <= INIT_INFLUENCER_COUNT; $i++) {
            $influencers[] = [
                $faker->userName(),
                $faker->word() . "i" . $i,
                "influencer" . $i . "@example.com",
                password_hash($faker->password(), PASSWORD_DEFAULT),
                $faker->text(User::$maxLens["profile_text"]),
                "INFLUENCER",
                DateOperator::formatDateTime(DateOperator::getCurrentDateTime()),
            ];
        }

        return $influencers;
    }
}

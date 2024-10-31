<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;
use Database\MySQLWrapper;
use Database\SchemaSeeder;

class Seed extends AbstractCommand {
    // コマンド名を設定
    protected static ?string $alias = "seed";

    public static function getArguments(): array {
        return [
            // TODO: descriptionの修正
            (new Argument("init"))->description("Execute initial data seeding.")->required(false)->allowAsShort(true),
            (new Argument("batch"))->description("Execute periodic data seeding.")->required(false)->allowAsShort(true),
        ];
    }

    public function execute(): int {
        $this->log("Starting data seeding...");

        $init = $this->getArgumentValue("init");
        $batch = $this->getArgumentValue("batch");

        $seedFiles = [];
        if ($init) {
            $seedFiles = [
                "InfluencerInitSeeder.php",
                "InfluencerPostInitSeeder.php",
                "InfluencerReplyInitSeeder.php",
                "InfluencerFollowInitSeeder.php",
                "UserInitSeeder.php",
                "UserPostInitSeeder.php",
                "UserReplyInitSeeder.php",
                "UserFollowInitSeeder.php",
                "LikeInitSeeder.php",
            ];
        } else if ($batch) {
            $seedFiles = [
                "InfluencerPostBatchSeeder.php",
                "UserPostBatchSeeder.php",
                "UserReplyBatchSeeder.php",
                "UserLikeBatchSeeder.php",
            ];
        }

        $this->runAllSeeds($seedFiles);

        $this->log("Data seeding completed successfully.");
        return 0;
    }

    function runAllSeeds(?array $seedFiles): void {
        $directoryPath = __DIR__ . "/../../Database/Seeds";

        // ディレクトリをスキャンしてすべてのファイルを取得
        $files = $seedFiles;
        if ($files === null || count($files) === 0) {
            $files = scandir($directoryPath);
        }

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
                // ファイル名からクラス名を抽出
                $className = "Database\Seeds\\" . pathinfo($file, PATHINFO_FILENAME);

                // シードファイルをインクルード
                include_once $directoryPath . "/" . $file;

                if (class_exists($className) && is_subclass_of($className, SchemaSeeder::class)) {
                    $seeder = new $className(new MySQLWrapper());
                    $seeder->seed();
                }
                else throw new \Exception("Seeder must be a class that subclasses the seeder interface");
            }
        }
    }
}

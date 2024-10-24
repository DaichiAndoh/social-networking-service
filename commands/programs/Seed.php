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
            (new Argument("init"))->description("Create initial records.")->required(false)->allowAsShort(true),
        ];
    }

    public function execute(): int {
        $init = $this->getArgumentValue("init");

        if ($init) {
            $seedFiles = [
                "InfluencerInitSeeder.php",
                "InfluencerPostInitSeeder.php",
                "InfluencerReplyInitSeeder.php",
                "InfluencerFollowInitSeeder.php",
            ];
            $this->runAllSeeds($seedFiles);
        } else {
            $this->runAllSeeds();
        }
        return 0;
    }

    function runAllSeeds(?array $seedFiles): void {
        $directoryPath = __DIR__ . "/../../database/seeds";

        // ディレクトリをスキャンしてすべてのファイルを取得
        $files = $seedFiles;
        if ($files === null) {
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

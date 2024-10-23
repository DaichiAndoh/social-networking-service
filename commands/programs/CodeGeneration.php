<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;

class CodeGeneration extends AbstractCommand {
    protected static ?string $alias = "code-gen";
    protected static bool $requiredCommandValue = true;

    public static function getArguments(): array {
        return [
            (new Argument("name"))->description("Name of the file that is to be generated.")->required(true),
        ];
    }

    public function execute(): int {
        $codeGenType = $this->getCommandValue();
        $this->log("Generating code for......." . $codeGenType);

        if ($codeGenType === "migration") {
            $migrationName = $this->getArgumentValue("name");
            $this->generateMigrationFile($migrationName);
        } else if ($codeGenType === "seeder") {
            $seederName = $this->getArgumentValue("name");
            $this->generateSeederFile($seederName);
        }

        return 0;
    }

    private function generateMigrationFile(string $migrationName): void {
        $filename = sprintf(
            "%s_%s_%s.php",
            date("Y-m-d"),
            time(),
            $migrationName
        );

        // マイグレーションファルコンテンツを取得
        $migrationContent = $this->getMigrationContent($migrationName);

        // マイグレーションファイルのパスを指定
        $path = sprintf("%s/../../database/migrations/%s", __DIR__, $filename);

        // マイグレーションファイルを作成
        file_put_contents($path, $migrationContent);
        $this->log("Migration file {$filename} has been generated!");
    }

    private function getMigrationContent(string $migrationName): string {
        $className = $this->pascalCase($migrationName);

        return <<<MIGRATION
<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class {$className} implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [];
    }
}
MIGRATION;
    }

    private function generateSeederFile(string $seederName): void {
        if (substr($seederName, -6) !== "Seeder") {
            $seederName .= "Seeder";
        }

        $filename = sprintf("%s.php", $seederName);

        // シードファルコンテンツを取得
        $seederContent = $this->getSeederContent($seederName);

        // シードファイルのパスを指定
        $path = sprintf("%s/../../database/seeds/%s", __DIR__, $filename);

        // シードファイルを作成
        file_put_contents($path, $seederContent);
        $this->log("Seeder file {$filename} has been generated!");
    }

    private function getSeederContent(string $seederName): string {
        $className = $this->pascalCase($seederName);

        return <<<SEEDER
<?php

namespace Database\Seeds;

use Faker\Factory;
use Database\AbstractSeeder;

class {$className} extends AbstractSeeder {
    // TODO: tableName文字列の割り当て
    protected ?string \$tableName = null;

    // TODO: tableColumns配列の割り当て
    protected array \$tableColumns = [];

    public function createRowData(): array {
        // TODO: createRowData()メソッドの実装
        return [];
    }
}
SEEDER;
    }

    private function pascalCase(string $string): string {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $string)));
    }
}

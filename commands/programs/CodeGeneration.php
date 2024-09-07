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

    private function pascalCase(string $string): string {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $string)));
    }
}

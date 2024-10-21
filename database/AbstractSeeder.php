<?php
namespace Database;

use Database\MySQLWrapper;

abstract class AbstractSeeder implements SchemaSeeder {
    protected MySQLWrapper $conn;
    protected ?string $tableName = null;

    // テーブルカラムは、"data_type" と "column_name" を含む連想配列の配列
    protected array $tableColumns = [];

    // 使用可能なカラムのタイプ
    // これらはバリデーションチェックとbind_param()のために使用される
    // キーはタイプの文字列で、値はbind_paramの文字列
    const AVAILABLE_TYPES = [
        "int" => "i",
        "float" => "d",
        "string" => "s",
    ];

    public function __construct(MySQLWrapper $conn) {
        $this->conn = $conn;
    }

    public function seed(): void {
        $data = $this->createRowData();

        if($this->tableName === null) throw new \Exception("Class requires a table name");
        if(empty($this->tableColumns)) throw new \Exception("Class requires a columns");

        foreach ($data as $row) {
            // 行を検証し、問題がなければ行を挿入
            $this->validateRow($row);
            $this->insertRow($row);
        }
    }

    // 各行をtableColumnsと照らし合わせて検証する
    protected function validateRow(array $row): void {
        if (count($row) !== count($this->tableColumns)) throw new \Exception("Row does not match the table columns");

        foreach ($row as $i=>$value) {
            $columnDataType = $this->tableColumns[$i]["data_type"];
            $columnName = $this->tableColumns[$i]["column_name"];
            $nullable = $this->tableColumns[$i]["nullable"];

            if (!isset(static::AVAILABLE_TYPES[$columnDataType])) throw new \InvalidArgumentException(sprintf("The data type %s is not an available data type.", $columnDataType));

            if ($nullable) {
                $valueType = get_debug_type($value);
                if ($valueType !== $columnDataType && $valueType !== "null") throw new \InvalidArgumentException(sprintf("Value for %s should be of type %s or null. Here is the current value: %s", $columnName, $columnDataType, json_encode($value)));
            } else {
                $valueType = get_debug_type($value);
                if ($valueType !== $columnDataType) throw new \InvalidArgumentException(sprintf("Value for %s should be of type %s. Here is the current value: %s", $columnName, $columnDataType, json_encode($value)));
            }
        }
    }

    // 各行をテーブルに挿入する
    protected function insertRow(array $row): void {
        // カラム名を取得
        $columnNames = array_map(function($columnInfo){ return $columnInfo["column_name"]; }, $this->tableColumns);

        // count($row)のプレースホルダー "?" を作成
        $placeholders = str_repeat("?,", count($row) - 1) . "?";

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            implode(", ", $columnNames),
            $placeholders
        );

        $stmt = $this->conn->prepare($sql);

        $dataTypes = implode(array_map(function($columnInfo){ return static::AVAILABLE_TYPES[$columnInfo["data_type"]];}, $this->tableColumns));

        $stmt->bind_param($dataTypes, ...array_values($row));

        $stmt->execute();
    }
}

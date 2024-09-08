<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateUsersTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "CREATE TABLE users (
                user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                username VARCHAR(20) NOT NULL UNIQUE,
                email VARCHAR(320) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                profile_text VARCHAR(160) NULL,
                profile_image_hash VARCHAR(40) NULL,
                email_confirmed_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "DROP TABLE users"
        ];
    }
}

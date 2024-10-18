<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class UpdateUsersTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "ALTER TABLE users ADD COLUMN type ENUM('USER', 'GUEST', 'INFLUENCER') NOT NULL DEFAULT 'user' AFTER profile_image_hash"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "ALTER TABLE users DROP COLUMN type"
        ];
    }
}

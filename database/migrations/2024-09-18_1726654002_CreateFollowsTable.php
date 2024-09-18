<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateFollowsTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "CREATE TABLE follows (
                follow_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                follower_id INT UNSIGNED NOT NULL,
                followee_id INT UNSIGNED NOT NULL,
                FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (followee_id) REFERENCES users(user_id) ON DELETE CASCADE
            )"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "DROP TABLE follows"
        ];
    }
}

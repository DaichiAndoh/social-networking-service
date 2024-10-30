<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateLikesTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "CREATE TABLE likes (
                like_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                post_id INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
            )"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "DROP TABLE likes"
        ];
    }
}

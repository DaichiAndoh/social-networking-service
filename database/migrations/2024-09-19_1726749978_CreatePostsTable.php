<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreatePostsTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "CREATE TABLE posts (
                post_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                reply_to_id INT UNSIGNED NULL,
                content VARCHAR(140) NOT NULL,
                image_hash VARCHAR(40) NULL,
                status ENUM('POSTED', 'SAVED', 'SCHEDULED') NOT NULL,
                scheduled_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (reply_to_id) REFERENCES posts(post_id) ON DELETE CASCADE
            )"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "DROP TABLE posts"
        ];
    }
}

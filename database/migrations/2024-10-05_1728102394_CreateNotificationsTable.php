<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateNotificationsTable implements SchemaMigration {
    public function up(): array {
        // TODO: マイグレーションロジックを追加
        return [
            "CREATE TABLE notifications (
                notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                from_user_id INT UNSIGNED NOT NULL,
                to_user_id INT UNSIGNED NOT NULL,
                source_id INT UNSIGNED NULL,
                type ENUM('FOLLOW','LIKE','REPLY','MESSAGE') NOT NULL,
                is_read BOOL NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (from_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (to_user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )"
        ];
    }

    public function down(): array {
        // TODO: ロールバックロジックを追加
        return [
            "DROP TABLE notifications"
        ];
    }
}

<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\NotificationDAO;
use Database\DatabaseManager;
use Models\Notification;

class NotificationDAOImpl implements NotificationDAO {
    public function create(Notification $notification): bool {
        if ($notification->getNotificationId() !== null) throw new \Exception("このNotificationデータを作成することはできません。: " . $notification->toString());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO notifications (from_user_id, to_user_id, source_id, type, is_read) VALUES (?, ?, ?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            "dddsd",
            [
                $notification->getFromUserId(),
                $notification->getToUserId(),
                $notification->getSourceId(),
                $notification->getType(),
                $notification->getIsRead(),
            ],
        );

        if (!$result) return false;

        $notification->setNotificationId($mysqli->insert_id);

        return true;
    }

    public function updateIsRead(Notification $notification): bool {
        if ($notification->getNotificationId() === null) throw new \Exception("IDを持たないデータは更新処理できません。");

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "UPDATE notifications SET is_read = ? WHERE notification_id = ?";

        $result = $mysqli->prepareAndExecute(
            $query,
            "ii",
            [
                $notification->getIsRead(),
                $notification->getNotificationId(),
            ],
        );

        if (!$result) return false;
        return true;
    }

    public function getUserNotifications(int $user_id, int $limit, int $offset): array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query =
            "SELECT n.notification_id, n.type notification_type, n.source_id, n.is_read, fu.name, fu.username, fu.profile_image_hash, fu.type user_type " .
            "FROM notifications n " .
            "INNER JOIN users fu ON fu.user_id = n.from_user_id " .
            "WHERE to_user_id = ? " .
            "ORDER BY n.created_at DESC " .
            "LIMIT ? OFFSET ?";

        $result = $mysqli->prepareAndFetchAll($query, "iii", [$user_id, $limit, $offset]);

        return $result ?? [];
    }

    public function getNotificationById(int $notification_id): ?Notification {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM notifications n WHERE notification_id = ? LIMIT 1";

        $result = $mysqli->prepareAndFetchAll($query, "i", [$notification_id]);

        return $result ? $this->rawDataToNotification($result[0]) : null;
    }

    private function rawDataToNotification(array $rawData): Notification {
        return new Notification(
            notification_id: $rawData["notification_id"],
            from_user_id: $rawData["from_user_id"],
            to_user_id: $rawData["to_user_id"],
            source_id: $rawData["source_id"],
            type: $rawData["type"],
            is_read: $rawData["is_read"],
            created_at: $rawData["created_at"],
            updated_at: $rawData["updated_at"],
        );
    }

    public function getUserUnreadNotificationCount(int $user_id): int {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT COUNT(*) count FROM notifications WHERE to_user_id = ? AND is_read = False";

        $result = $mysqli->prepareAndFetchAll($query, "i", [$user_id]);

        return $result[0]["count"] ?? 0;
    }
}

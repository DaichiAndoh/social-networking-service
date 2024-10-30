<?php

namespace Database\DataAccess\Interfaces;

use Models\Notification;

interface NotificationDAO {
    public function create(Notification $notification): bool;
    public function updateIsRead(Notification $notification): bool;
    public function getUserNotifications(int $user_id, int $limit, int $offset): array;
    public function getNotificationById(int $notification_id): ?Notification;
    public function getUserUnreadNotificationCount(int $user_id): int;
}

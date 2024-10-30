<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Notification implements Model {
    use GenericModel;

    public function __construct(
        private int $from_user_id,
        private int $to_user_id,
        private string $type,
        private bool $is_read = false,
        private ?int $notification_id = null,
        private ?int $source_id = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
    ) {}

    public function getNotificationId(): ?int {
        return $this->notification_id;
    }

    public function setNotificationId(int $notification_id): void {
        $this->notification_id = $notification_id;
    }

    public function getFromUserId(): int {
        return $this->from_user_id;
    }

    public function setFromUserId(int $from_user_id): void {
        $this->from_user_id = $from_user_id;
    }

    public function getToUserId(): int {
        return $this->to_user_id;
    }

    public function setToUserId(int $to_user_id): void {
        $this->to_user_id = $to_user_id;
    }

    public function getSourceId(): ?int {
        return $this->source_id;
    }

    public function setSourceId(int $source_id): void {
        $this->source_id = $source_id;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(int $type): void {
        $this->type = $type;
    }

    public function getIsRead(): bool {
        return $this->is_read;
    }

    public function setIsRead(bool $is_read): void {
        $this->is_read = $is_read;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): void {
        $this->updated_at = $updated_at;
    }
}

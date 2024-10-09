<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Message implements Model {
    use GenericModel;

    public function __construct(
        private int $from_user_id,
        private int $to_user_id,
        private string $content,
        private ?int $message_id = null,
        private ?string $created_at = null,
    ) {}

    public function getMessageId(): ?int {
        return $this->message_id;
    }

    public function setMessageId(int $message_id): void {
        $this->message_id = $message_id;
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

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(int $content): void {
        $this->content = $content;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void {
        $this->created_at = $created_at;
    }
}

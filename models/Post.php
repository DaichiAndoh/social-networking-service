<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Post implements Model {
    use GenericModel;

    public static $minLens = [
        "content" => 1,
    ];

    public static $maxLens = [
        "content" => 140,
    ];

    public function __construct(
        private string $content,
        private string $status,
        private int $user_id,
        private ?int $post_id = null,
        private ?int $reply_to_id = null,
        private ?string $image_hash = null,
        private ?string $scheduled_at = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
    ) {}

    public function getPostId(): ?int {
        return $this->post_id;
    }

    public function setPostId(int $post_id): void {
        $this->post_id = $post_id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void {
        $this->user_id = $user_id;
    }

    public function getReplyToId(): ?int {
        return $this->reply_to_id;
    }

    public function setReplyToId(int $reply_to_id): void {
        $this->reply_to_id = $reply_to_id;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function getImageHash(): ?string {
        return $this->image_hash;
    }

    public function setImageHash(string $image_hash): void {
        $this->image_hash = $image_hash;
    }

    public function getScheduledAt(): ?string {
        return $this->scheduled_at;
    }

    public function setScheduledAt(string $scheduled_at): void {
        $this->scheduled_at = $scheduled_at;
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

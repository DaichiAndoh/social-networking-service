<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Like implements Model {
    use GenericModel;

    public function __construct(
        private int $user_id,
        private int $post_id,
        private ?int $like_id = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
    ) {}

    public function getLikeId(): ?int {
        return $this->like_id;
    }

    public function setLikeId(int $like_id): void {
        $this->like_id = $like_id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void {
        $this->user_id = $user_id;
    }

    public function getPostId(): int {
        return $this->post_id;
    }

    public function setPostId(int $post_id): void {
        $this->post_id = $post_id;
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

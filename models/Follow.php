<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Follow implements Model {
    use GenericModel;

    public function __construct(
        private int $follower_id,
        private int $followee_id,
        private ?int $follow_id = null,
    ) {}

    public function getFollowId(): ?int {
        return $this->follow_id;
    }

    public function setFollowId(int $follow_id): void {
        $this->follow_id = $follow_id;
    }

    public function getFollowerId(): int {
        return $this->follower_id;
    }

    public function setFollowerId(int $follower_id): void {
        $this->follower_id = $follower_id;
    }

    public function getFolloweeId(): int {
        return $this->followee_id;
    }

    public function setFolloweeId(int $followee_id): void {
        $this->followee_id = $followee_id;
    }
}

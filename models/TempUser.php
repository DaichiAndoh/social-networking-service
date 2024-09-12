<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class TempUser implements Model {
    use GenericModel;

    public function __construct(
        private int $user_id,
        private string $signature,
        private string $type,
        private ?int $temp_user_id = null,
        private ?string $created_at = null,
    ) {}

    public function getTempUserId(): ?int {
        return $this->temp_user_id;
    }

    public function setTempUserId(int $temp_user_id): void {
        $this->temp_user_id = $temp_user_id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getSignature(): string {
        return $this->signature;
    }

    public function getType(): string {
        return $this->type;
    }
}

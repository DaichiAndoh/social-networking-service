<?php

namespace Database\DataAccess\Interfaces;

use Models\Like;

interface LikeDAO {
    public function like(Like $follow): bool;
    public function unlike(int $user_id, int $post_id): bool;
    public function exists(int $user_id, int $post_id): bool;
}

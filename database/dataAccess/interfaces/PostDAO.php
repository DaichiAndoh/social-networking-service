<?php

namespace Database\DataAccess\Interfaces;

use Models\Post;

interface PostDAO {
    public function create(Post $post): bool;
    public function getTrendTimelinePosts(int $user_id, int $limit, int $offset): array;
    public function getFolloweeTimelinePosts(int $user_id, int $limit, int $offset): array;
}

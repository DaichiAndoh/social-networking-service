<?php

namespace Database\DataAccess\Interfaces;

use Models\Post;

interface PostDAO {
    public function create(Post $post): bool;
    public function getPost(int $post_id): ?array;
    public function getReplies(int $post_id, int $limit, int $offset): array;
    public function getTrendTimelinePosts(int $user_id, int $limit, int $offset): array;
    public function getFollowTimelinePosts(int $user_id, int $limit, int $offset): array;
}

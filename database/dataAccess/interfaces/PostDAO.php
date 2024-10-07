<?php

namespace Database\DataAccess\Interfaces;

use Models\Post;

interface PostDAO {
    public function create(Post $post): bool;
    public function delete(int $post_id): bool;
    public function getPost(int $post_id, int $authenticated_user_id): ?array;
    public function getReplies(int $post_id, int $authenticated_user_id, int $limit, int $offset): array;
    public function getTrendTimelinePosts(int $user_id, int $limit, int $offset): array;
    public function getFollowTimelinePosts(int $user_id, int $limit, int $offset): array;
    public function getUserPosts(int $user_id, int $limit, int $offset): array;
    public function getUserReplies(int $user_id, int $limit, int $offset): array;
    public function getUserLikes(int $user_id, int $limit, int $offset): array;
    public function getPostById(int $post_id): ?Post;
}

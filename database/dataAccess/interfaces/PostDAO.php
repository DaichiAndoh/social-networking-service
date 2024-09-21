<?php

namespace Database\DataAccess\Interfaces;

use Models\Post;

interface PostDAO {
    public function create(Post $post): bool;
}

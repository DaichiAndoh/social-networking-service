<?php

namespace Database\DataAccess\Interfaces;

use Models\Message;

interface MessageDAO {
    public function create(Message $message): bool;
    public function getChatUsers(int $user_id, int $limit, int $offset): array;
    public function getChatMessages(int $user_id, int $chat_user_id, int $limit, int $offset): array;
}

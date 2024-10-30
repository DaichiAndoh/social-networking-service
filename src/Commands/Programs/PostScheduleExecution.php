<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Database\DataAccess\DAOFactory;

class PostScheduleExecution extends AbstractCommand {
    // コマンド名を設定
    protected static ?string $alias = "post-schedule-exec";

    // 引数を割り当て
    public static function getArguments(): array {
        return [];
    }

    public function execute(): int {
        return $this->post();
    }

    private function post(): bool {
        $postDao = DAOFactory::getPostDAO();
        $result = $postDao->postScheduledPosts();

        if ($result) {
            $this->log("予約ポストの投稿処理に成功しました。");
            return 0;
        } else {
            $this->log("予約ポストの投稿処理に失敗しました。");
            return 1;
        }
    }
}

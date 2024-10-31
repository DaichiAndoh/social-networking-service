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
        $this->log("Starting scheduled post publishing...");

        $postDao = DAOFactory::getPostDAO();
        $result = $postDao->postScheduledPosts();

        if ($result) {
            $this->log("Scheduled post publishing completed successfully.");
            return 0;
        } else {
            $this->log("Scheduled post publishing failed.");
            return 1;
        }
    }
}

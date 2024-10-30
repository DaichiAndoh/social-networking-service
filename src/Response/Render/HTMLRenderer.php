<?php

namespace Response\Render;

use Database\DataAccess\DAOFactory;
use Helpers\Authenticator;
use Response\HTTPRenderer;

require_once(sprintf("%s/../../constants/file_constants.php", __DIR__));

class HTMLRenderer implements HTTPRenderer {
    private string $viewFile;
    private array $data;

    public function __construct(string $viewFile, array $data = []) {
        $this->viewFile = $viewFile;
        $this->data = $data;
    }

    public function getFields(): array {
        return [
            "Content-Type" => "text/html; charset=UTF-8",
        ];
    }

    public function getContent(): string {
        $viewFilePath = $this->getViewFilePath($this->viewFile);

        if (!file_exists($viewFilePath)) {
            throw new Exception("View file {$viewFielPath} does not exist.");
        }

        ob_start();
        extract($this->data);
        require $viewFilePath;
        return $this->getHeader() . ob_get_clean() . $this->getFooter();
    }

    private function getViewFilePath(string $viewFile): string {
        return sprintf("%s/%s/views/%s.php", __DIR__, "../..", $viewFile);
    }

    private function getHeader(): string {
        ob_start();
        $user = Authenticator::getAuthenticatedUser();
        $notificationCount = 0;
        if ($user !== null) {
            $notificationCount = DAOFactory::getNotificationDAO()->getUserUnreadNotificationCount($user->getUserId());
        }
        if ($user !== null) {
            $profileImagePath = $user->getProfileImageHash() ?
                PROFILE_IMAGE_FILE_DIR . $user->getProfileImageHash() :
                PROFILE_IMAGE_FILE_DIR . "default_profile_image.png";
        }
        require $this->getViewFilePath("layouts/header");
        require $this->getViewFilePath("components/message_boxes");
        require $this->getViewFilePath("components/post_modal");
        require $this->getViewFilePath("components/reply_modal");
        require $this->getViewFilePath("components/sidebar");
        return ob_get_clean();
    }

    private function getFooter(): string {
        ob_start();
        require $this->getViewFilePath("layouts/footer");
        return ob_get_clean();
    }
}

<?php

namespace Helpers;

require_once(sprintf("%s/../constants/file_constants.php", __DIR__));

class ImageOperator {
    public static function imageTypeToExtension(string $type): string {
        return str_replace("image/", "", $type);
    }

    public static function savePostImage(string $uploadedImagePath, string $extension, string $username): string {
        $hash = md5($username . date("Y-m-d H:i:s"));
        $imageHash = $hash . "." . $extension;
        $originalImagePath = sprintf("%s/../public%s%s", __DIR__, POST_ORIGINAL_IMAGE_FILE_DIR, $imageHash);
        move_uploaded_file($uploadedImagePath, $originalImagePath);
        self::saveThumbnailImage($originalImagePath, $imageHash);
        return $imageHash;
    }

    private static function saveThumbnailImage(string $originalImagePath, string $imageHash): void {
        $thumbnailImagePath = sprintf("%s/../public%s%s", __DIR__, POST_THUMBNAIL_IMAGE_FILE_DIR, $imageHash);

        $output=null;
        $retval=null;
        $command = sprintf("convert %s -resize 300x %s", $originalImagePath, $thumbnailImagePath);
        exec($command, $output, $retval);
    }

    public static function deletePostImage(string $imageHash): void {
        $path = sprintf("%s/../public%s%s", __DIR__, POST_ORIGINAL_IMAGE_FILE_DIR, $imageHash);
        if (file_exists($path)) unlink($path);

        $path = sprintf("%s/../public%s%s", __DIR__, POST_THUMBNAIL_IMAGE_FILE_DIR, $imageHash);
        if (file_exists($path)) unlink($path);
    }

    public static function saveProfileImage(string $uploadedImagePath, string $extension, string $username): string {
        $hash = md5($username . date("Y-m-d H:i:s"));
        $imageHash = $hash . "." . $extension;
        $imagePath = sprintf("%s/../public%s%s", __DIR__, PROFILE_IMAGE_FILE_DIR, $imageHash);
        move_uploaded_file($uploadedImagePath, $imagePath);
        return $imageHash;
    }

    public static function deleteProfileImage(string $imageHash): void {
        $path = sprintf("%s/../public%s%s", __DIR__, PROFILE_IMAGE_FILE_DIR, $imageHash);
        if (file_exists($path)) unlink($path);
    }
}

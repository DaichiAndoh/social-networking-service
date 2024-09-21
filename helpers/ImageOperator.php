<?php

namespace Helpers;

class ImageOperator {
    public static function imageTypeToExtension(string $type): string {
        return str_replace("image/", "", $type);
    }

    public static function savePostImage(string $uploadedImagePath, string $extension, string $username): string {
        $hash = md5($username . date("Y-m-d H:i:s"));
        $imageHash = $hash . "." . $extension;
        $imagePath = sprintf("%s/../public/img/post/%s", __DIR__, $imageHash);
        move_uploaded_file($uploadedImagePath, $imagePath);
        return $imageHash;
    }

    public static function saveProfileImage(string $uploadedImagePath, string $extension, string $username): string {
        $hash = md5($username . date("Y-m-d H:i:s"));
        $imageHash = $hash . "." . $extension;
        $imagePath = sprintf("%s/../public/img/user/%s", __DIR__, $imageHash);
        move_uploaded_file($uploadedImagePath, $imagePath);
        return $imageHash;
    }

    public static function deleteProfileImage(string $imageHash): void {
        $path = sprintf("%s/../public/img/user/%s", __DIR__, $imageHash);
        if (file_exists($path)) unlink($path);
    }
}

<?php

namespace Database\DataAccess;

use Database\DataAccess\Implementations\FollowDAOImpl;
use Database\DataAccess\Implementations\LikeDAOImpl;
use Database\DataAccess\Implementations\NotificationDAOImpl;
use Database\DataAccess\Implementations\PostDAOImpl;
use Database\DataAccess\Implementations\TempUserDAOImpl;
use Database\DataAccess\Implementations\UserDAOImpl;
use Database\DataAccess\Interfaces\FollowDAO;
use Database\DataAccess\Interfaces\LikeDAO;
use Database\DataAccess\Interfaces\NotificationDAO;
use Database\DataAccess\Interfaces\PostDAO;
use Database\DataAccess\Interfaces\TempUserDAO;
use Database\DataAccess\Interfaces\UserDAO;
use Helpers\ConfigReader;

class DAOFactory {
    public static function getUserDAO(): UserDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new UserDAOImpl(),
            default => new UserDAOImpl(),
        };
    }

    public static function getTempUserDAO(): TempUserDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new TempUserDAOImpl(),
            default => new TempUserDAOImpl(),
        };
    }

    public static function getFollowDAO(): FollowDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new FollowDAOImpl(),
            default => new FollowDAOImpl(),
        };
    }

    public static function getPostDAO(): PostDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new PostDAOImpl(),
            default => new PostDAOImpl(),
        };
    }

    public static function getLikeDAO(): LikeDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new LikeDAOImpl(),
            default => new LikeDAOImpl(),
        };
    }

    public static function getNotificationDAO(): NotificationDAO {
        $driver = ConfigReader::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new NotificationDAOImpl(),
            default => new NotificationDAOImpl(),
        };
    }
}

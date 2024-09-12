<?php

namespace Database\DataAccess;

use Database\DataAccess\Implementations\TempUserDAOImpl;
use Database\DataAccess\Implementations\UserDAOImpl;
use Database\DataAccess\Interfaces\TempUserDAO;
use Database\DataAccess\Interfaces\UserDAO;
use Helpers\Settings;

class DAOFactory {
    public static function getUserDAO(): UserDAO {
        $driver = Settings::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new UserDAOImpl(),
            default => new UserDAOImpl(),
        };
    }

    public static function getTempUserDAO(): TempUserDAO {
        $driver = Settings::env("DATABASE_DRIVER");

        return match ($driver) {
            "mysql" => new TempUserDAOImpl(),
            default => new TempUserDAOImpl(),
        };
    }
}

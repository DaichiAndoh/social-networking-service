<?php

namespace Database\DataAccess;

use Database\DataAccess\Implementations\UserDAOImpl;
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
}

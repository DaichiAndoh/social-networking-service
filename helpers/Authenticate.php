<?php

namespace Helpers;

use Exceptions\AuthenticationFailureException;
use Database\DataAccess\DAOFactory;
use Models\User;

class Authenticate {
    // 認証されたユーザーの状態をこのクラス変数に保持する
    private static ?User $authenticatedUser = null;
    private const USER_ID_SESSION_KEY = "user_id";

    public static function loginAsUser(User $user): bool {
        if ($user->getUserId() === null) throw new \Exception("Cannot login a user with no ID.");
        if (isset($_SESSION[self::USER_ID_SESSION_KEY])) throw new \Exception("User is already logged in. Logout before continuing.");

        $_SESSION[self::USER_ID_SESSION_KEY] = $user->getUserId();
        return true;
    }

    public static function logoutUser(): bool {
        if (isset($_SESSION[self::USER_ID_SESSION_KEY])) {
            unset($_SESSION[self::USER_ID_SESSION_KEY]);
            self::$authenticatedUser = null;
            return true;
        }
        else throw new \Exception("No user to logout.");
    }

    private static function retrieveAuthenticatedUser(): void {
        if (!isset($_SESSION[self::USER_ID_SESSION_KEY])) return;
        $userDao = DAOFactory::getUserDAO();
        self::$authenticatedUser = $userDao->getById($_SESSION[self::USER_ID_SESSION_KEY]);
    }

    public static function isLoggedIn(): bool {
        self::retrieveAuthenticatedUser();
        return self::$authenticatedUser !== null;
    }

    public static function emailVerified(): bool {
        return self::isLoggedIn() && self::$authenticatedUser->getEmailConfirmedAt() !== null;
    }

    public static function getAuthenticatedUser(): ?User {
        self::retrieveAuthenticatedUser();
        return self::$authenticatedUser;
    }

    /**
     * @throws AuthenticationFailureException
     */
    public static function authenticate(string $email, string $password): User {
        $userDAO = DAOFactory::getUserDAO();

        // メールアドレスでユーザー取得
        self::$authenticatedUser = $userDAO->getByEmail($email);
        if (self::$authenticatedUser === null) throw new AuthenticationFailureException();

        // パスワードを取得
        $hashedPassword = $userDAO->getHashedPasswordById(self::$authenticatedUser->getUserId());
        if (password_verify($password, $hashedPassword)) {
            self::loginAsUser(self::$authenticatedUser);
            return self::$authenticatedUser;
        }
        else throw new AuthenticationFailureException();
    }
}

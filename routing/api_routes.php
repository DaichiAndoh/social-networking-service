<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticate;
use Helpers\ValidationHelper;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;
use Types\ValueType;

return [
    "/api" => function(): HTTPRenderer {
        return new JSONRenderer(["page" => "top"]);
    },
    "/api/register" => function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // ユーザが現在ログインしている場合、登録ページにアクセスすることは不可
            if (Authenticate::isLoggedIn()) throw new Exception("Already logged in.");

            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("Invalid request method!");

            $userDao = DAOFactory::getUserDAO();

            // 入力値検証
            $fieldErrors = ValidationHelper::validateFields([
                "name" => ValueType::STRING,
                "username" => ValueType::STRING,
                "email" => ValueType::EMAIL,
                "password" => ValueType::PASSWORD,
                "confirm-password" => ValueType::PASSWORD,
            ], $_POST);

            if (!isset($fieldErrors["confirm-password"]) && $_POST["confirm-password"] !== $_POST["password"]) {
                $fieldErrors["confirm-password"] = "パスワードと一致していません。";
            }

            if (!isset($fieldErrors["email"]) && $userDao->getByEmail($_POST["email"])){
                $fieldErrors["email"] = "このメールアドレスは使用できません。";
            }

            if (!isset($fieldErrors["username"]) && $userDao->getByEmail($_POST["username"])){
                $fieldErrors["username"] = "このユーザー名は使用できません。";
            }

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // 新しいUserオブジェクトを作成
            $user = new User(
                name: $_POST["name"],
                username: $_POST["username"],
                email: $_POST["email"],
            );

            // データベースにユーザーを作成
            $success = $userDao->create($user, $_POST["password"]);

            if (!$success) throw new Exception("Failed to create new user!");

            // ユーザーログイン
            Authenticate::loginAsUser($user);

            // UI側で作成後のページに遷移されるため、そこでこのメッセージが表示される
            FlashData::setFlashData("success", "Account successfully created.");
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "An error occurred.";
            return new JSONRenderer($resBody);
        }
    },
    "/api/login" => function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // ユーザが現在ログインしている場合、以降の処理は行わない
            if (Authenticate::isLoggedIn()) throw new Exception("Already logged in.");

            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method!');

            $userDao = DAOFactory::getUserDAO();

            // 入力値検証
            $fieldErrors = ValidationHelper::validateFields([
                "email" => ValueType::EMAIL,
                "password" => ValueType::STRING,
            ], $_POST);

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // 入力値でユーザー認証を行う
            Authenticate::authenticate($_POST["email"], $_POST["password"]);

            // UI側で作成後のページに遷移されるため、そこでこのメッセージが表示される
            FlashData::setFlashData("success", "Logged in successfully.");
            return new JSONRenderer($resBody);
        } catch (AuthenticationFailureException $e) {
            $resBody["success"] = false;
            $resBody["fieldErrors"] = [
                "email" => "メールアドレスまたはパスワードが不適切です。",
                "password" => "メールアドレスまたはパスワードが不適切です。",
            ];
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "An error occurred.";
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    },
    "/api/logout" => function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            Authenticate::logoutUser();
            FlashData::setFlashData("success", "Logged out.");
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "An error occurred.";
            return new JSONRenderer($resBody);
        }
    },
];

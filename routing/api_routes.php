<?php

use Database\DataAccess\DAOFactory;
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

            if (!isset($fieldErrors["confirm-password"]) && $_POST["confirm_password"] !== $_POST["password"]) {
                $fieldErrors["confirm_password"] = "パスワードと一致していません。";
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
                name: $validatedData["name"],
                username: $validatedData["username"],
                email: $validatedData["email"],
            );

            // データベースにユーザーを作成
            $success = $userDao->create($user, $validatedData["password"]);

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
];

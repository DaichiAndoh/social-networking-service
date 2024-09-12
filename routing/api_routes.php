<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticate;
use Helpers\MailSend;
use Helpers\ValidationHelper;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;
use Routing\Route;
use Types\ValueType;

return [
    "/api/register" => Route::create("/api/register", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();

            // 入力値検証
            $fieldErrors = ValidationHelper::validateFields([
                "name" => ValueType::STRING,
                "username" => ValueType::STRING,
                "email" => ValueType::EMAIL,
                "password" => ValueType::PASSWORD,
                "confirm-password" => ValueType::PASSWORD,
            ], $_POST);

            if (
                !isset($fieldErrors["name"]) &&
                !ValidationHelper::validateStrLen($_POST["name"], User::$minLens["name"], User::$maxLens["name"])
            ) $fieldErrors["name"] = sprintf(
                "%s文字以上、%s文字以下で入力してください。",
                User::$minLens["name"],
                User::$maxLens["name"],
            );

            if (!isset($fieldErrors["username"])) {
                if (!ValidationHelper::validateStrLen($_POST["username"], User::$minLens["username"], User::$maxLens["username"])) {
                    $fieldErrors["username"] = sprintf(
                        "%s文字以上、%s文字以下で入力してください。",
                        User::$minLens["username"],
                        User::$maxLens["username"],
                    );
                } else if ($userDao->getByUsername($_POST["username"])){
                    $fieldErrors["username"] = "このユーザー名は使用できません。";
                }
            }

            if (!isset($fieldErrors["email"])) {
                if (!ValidationHelper::validateStrLen($_POST["email"], User::$minLens["email"], User::$maxLens["email"])) {
                    $fieldErrors["email"] = sprintf(
                        "%s文字以上、%s文字以下で入力してください。",
                        User::$minLen["email"],
                        User::$maxLen["email"],
                    );
                } else if ($userDao->getByEmail($_POST["email"])) {
                    $fieldErrors["email"] = "このメールアドレスは使用できません。";
                }
            }

            if (!isset($fieldErrors["confirm-password"]) && $_POST["confirm-password"] !== $_POST["password"]) {
                $fieldErrors["confirm-password"] = "パスワードと一致していません。";
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

            // DB登録
            $userDao = DAOFactory::getUserDAO();
            $success = $userDao->create($user, $_POST["password"]);
            if (!$success) throw new Exception("アカウント仮登録処理に失敗しました。");

            // メール検証用URLを作成
            $queryParameters = [
                "id" => $user->getUserId(),
                "user"=> $user->getEmail(),
                "expiration" => time() + 3600,
            ];
            $signedURL = Route::create("/verify_email", function() {})->getSignedURL($queryParameters);

            // 検証メールを送信
            $sendResult = MailSend::sendVerificationMail(
                $signedURL,
                "[SNS] メールアドレスを確認してください。",
                $user->getEmail(),
                $user->getName()
            );
            if (!$sendResult) new Exception("メールアドレス検証メールの送信に失敗しました。");

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_guest"]),
    "/api/login" => Route::create("/api/login", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

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
            FlashData::setFlashData("success", "ログインしました。");
            $resBody["redirectUrl"] = "/timeline";
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
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_guest"]),
    "/api/logout" => Route::create("/api/logout", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            Authenticate::logoutUser();
            FlashData::setFlashData("success", "ログアウトしました。");
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth"]),
];

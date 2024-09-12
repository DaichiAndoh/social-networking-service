<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticate;
use Helpers\MailSend;
use Helpers\ValidationHelper;
use Models\TempUser;
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
            $tempUserDao = DAOFactory::getTempUserDAO();

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

            // userを作成
            $userDao = DAOFactory::getUserDAO();
            $success = $userDao->create($user, $_POST["password"]);
            if (!$success) throw new Exception("userの作成に失敗しました。");
            else Authenticate::loginAsUser($user);

            // メール検証用URLを作成
            $queryParameters = [
                "user"=> $user->getEmail(),
                "expiration" => time() + 3600,
            ];
            $signedURLData = Route::create("/verify_email", function() {})->getSignedURL($queryParameters);

            // temp_userを作成
            $tempUser = new TempUser(
                user_id: $user->getUserId(),
                signature: $signedURLData["signature"],
                type: "EMAIL_VERIFICATION",
            );
            $result = $tempUserDao->create($tempUser);
            if (!$result) throw new Exception("temp_userの作成に失敗しました。");

            // 検証メールを送信
            $sendResult = MailSend::sendVerificationMail(
                $signedURLData["url"],
                $user->getEmail(),
                $user->getName(),
                "email_verification"
            );
            if (!$sendResult) new Exception("メールアドレス検証メールの送信に失敗しました。");

            $resBody["redirectUrl"] = "/verify_resend";
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
    "/api/email/verification/resend" => Route::create("/api/email/verification/resend", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            // 検証用URLを作成
            $authenticatedUser = Authenticate::getAuthenticatedUser();
            $queryParameters = [
                "user"=> $authenticatedUser->getEmail(),
                "expiration" => time() + 1800,
            ];
            $signedURLData = Route::create("/email/verify", function() {})->getSignedURL($queryParameters);

            // 検証用メールを送信
            $sendResult = MailSend::sendVerificationMail(
                $signedURLData["url"],
                $authenticatedUser->getEmail(),
                $authenticatedUser->getName(),
                "password_reset"
            );
            if (!$sendResult) new Exception("メールアドレス検証用メールの送信に失敗しました。");

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_unverified"]),
    "/api/password_forget" => Route::create("/api/password_forget", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // 入力値検証
            $fieldErrors = ValidationHelper::validateFields([
                "email" => ValueType::EMAIL,
            ], $_POST);

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // ユーザーを取得
            $user = $userDao->getByEmail($_POST["email"]);
            if ($user === null) {
                $fieldErrors["email"] = "入力されたメールアドレスのユーザーが見つかりません。";
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // パスワードリセット用URLを作成
            $queryParameters = [
                "user"=> $user->getEmail(),
                "expiration" => time() + 3600,
            ];
            $signedURLData = Route::create("/password_reset", function() {})->getSignedURL($queryParameters);

            // temp_userを作成
            $tempUser = new TempUser(
                user_id: $user->getUserId(),
                signature: $signedURLData["signature"],
                type: "PASSWORD_RESET",
            );
            $result = $tempUserDao->create($tempUser);
            if (!$result) throw new Exception("temp_userの作成に失敗しました。");

            // 検証メールを送信
            $sendResult = MailSend::sendVerificationMail(
                $signedURLData["url"],
                $user->getEmail(),
                $user->getName(),
                "password_reset"
            );
            if (!$sendResult) new Exception("パスワード変更メールの送信に失敗しました。");

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_guest"]),
    "/api/password_reset" => Route::create("/api/password_reset", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // 入力値検証
            $fieldErrors = ValidationHelper::validateFields([
                "signature" => ValueType::STRING,
                "password" => ValueType::PASSWORD,
                "confirm-password" => ValueType::PASSWORD,
            ], $_POST);

            if (!isset($fieldErrors["confirm-password"]) && $_POST["confirm-password"] !== $_POST["password"]) {
                $fieldErrors["confirm-password"] = "パスワードと一致していません。";
            }

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // signatureに紐づくユーザーを取得
            $tempUser = $tempUserDao->getBySignature($_POST["signature"]);
            if ($tempUser === null || $tempUser->getType() !== "PASSWORD_RESET") {
                throw new Exception("署名に紐づくユーザーが存在しません。");
            }

            // パスワードを更新
            $user = $userDao->getById($tempUser->getUserId());
            $result = $userDao->updatePassword($user->getUserId(), $_POST["password"]);
            if (!$result) throw new Exception("パスワードの更新処理に失敗しました。");

            $tempUserDao->deleteTempUserById($tempUser->getTempUserId());

            // UI側で作成後のページに遷移されるため、そこでこのメッセージが表示される
            FlashData::setFlashData("success", "パスワードを変更しました。");
            $resBody["redirectUrl"] = "/login";
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

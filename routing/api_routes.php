<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticator;
use Helpers\Hasher;
use Helpers\ImageOperator;
use Helpers\MailSender;
use Helpers\Validator;
use Models\TempUser;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;
use Routing\Route;
use Types\ValueType;

require_once(sprintf("%s/../constants/file_constants.php", __DIR__));

return [
    "/api/register" => Route::create("/api/register", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // 入力値検証
            $fieldErrors = Validator::validateFields([
                "name" => ValueType::STRING,
                "username" => ValueType::STRING,
                "email" => ValueType::EMAIL,
                "password" => ValueType::PASSWORD,
                "confirm-password" => ValueType::PASSWORD,
            ], $_POST);

            if (
                !isset($fieldErrors["name"]) &&
                !Validator::validateStrLen($_POST["name"], User::$minLens["name"], User::$maxLens["name"])
            ) $fieldErrors["name"] = sprintf(
                "%s文字以上、%s文字以下で入力してください。",
                User::$minLens["name"],
                User::$maxLens["name"],
            );

            if (!isset($fieldErrors["username"])) {
                if (!Validator::validateStrLen($_POST["username"], User::$minLens["username"], User::$maxLens["username"])) {
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
                if (!Validator::validateStrLen($_POST["email"], User::$minLens["email"], User::$maxLens["email"])) {
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
            if ($success) Authenticator::loginAsUser($user);
            else throw new Exception("ユーザー登録に失敗しました。");

            // メール検証用URLを作成
            $queryParameters = [
                "user"=> Hasher::createHash($user->getEmail()),
                "expiration" => time() + 1800,
            ];
            $signedURLData = Route::create("/email/verify", function() {})->getSignedURL($queryParameters);

            // 検証メールを送信
            $sendResult = MailSender::sendEmailVerificationMail(
                $signedURLData["url"],
                $user->getEmail(),
                $user->getName(),
            );
            if (!$sendResult) new Exception("メールアドレス検証メールの送信に失敗しました。");

            $resBody["redirectUrl"] = "/email/verification/resend";
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
            $fieldErrors = Validator::validateFields([
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
            Authenticator::authenticate($_POST["email"], $_POST["password"]);

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
            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $queryParameters = [
                "user"=> Hasher::createHash($authenticatedUser->getEmail()),
                "expiration" => time() + 1800,
            ];
            $signedURLData = Route::create("/email/verify", function() {})->getSignedURL($queryParameters);

            // 検証用メールを送信
            $sendResult = MailSender::sendEmailVerificationMail(
                $signedURLData["url"],
                $authenticatedUser->getEmail(),
                $authenticatedUser->getName(),
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
    "/api/password/forgot" => Route::create("/api/password/forgot", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // 入力値検証
            $fieldErrors = Validator::validateFields([
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
                "user"=> Hasher::createHash($user->getEmail()),
                "expiration" => time() + 3600,
            ];
            $signedURLData = Route::create("/password/reset", function() {})->getSignedURL($queryParameters);

            // 一時ユーザーを作成
            $tempUser = new TempUser(
                user_id: $user->getUserId(),
                signature: $signedURLData["signature"],
                type: "PASSWORD_RESET",
            );
            $result = $tempUserDao->create($tempUser);
            if (!$result) throw new Exception("一時ユーザーの作成に失敗しました。");

            // 検証メールを送信
            $sendResult = MailSender::sendPasswordResetMail(
                $signedURLData["url"],
                $user->getEmail(),
                $user->getName(),
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
    "/api/password/reset" => Route::create("/api/password/reset", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // 入力値検証
            $fieldErrors = Validator::validateFields([
                "user" => ValueType::STRING,
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
            $user = $userDao->getById($tempUser->getUserId());

            // 署名に紐づくメールアドレスがユーザーのメールアドレスと同じかを確認
            $hashedEmail = Hasher::createHash($user->getEmail());
            $expectedHashedEmail = $_POST["user"];
            if (!Hasher::isHashEqual($expectedHashedEmail, $hashedEmail)) {
                throw new Exception("署名に紐づくメールアドレスがパスワード更新対象ユーザーのメールアドレスと一致しません。");
            }

            // パスワードを更新
            $result = $userDao->updatePassword($user->getUserId(), $_POST["password"]);
            if (!$result) throw new Exception("パスワードの更新処理に失敗しました。");

            // 一時ユーザーを削除
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
            Authenticator::logoutUser();
            FlashData::setFlashData("success", "ログアウトしました。");
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth"]),

    "/api/user/profile/init" => Route::create("/api/user/profile/init", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            $user = Authenticator::getAuthenticatedUser();
            $userData = [
                "name" => $user->getName(),
                "username" => $user->getUsername(),
                "profileText" => $user->getProfileText(),
                "profileImagePath" => $user->getProfileImageHash() ?
                    PROFILE_IMAGE_FILE_DIR . $user->getProfileImageHash() :
                    PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                "profileImageType" => $user->getProfileImageHash() === null ? "default" : "custom",
            ];
            $resBody["userData"] = $userData;

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/profile/edit" => Route::create("/api/user/profile/edit", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $user = Authenticator::getAuthenticatedUser();
            $userDao = DAOFactory::getUserDAO();

            // 入力値検証
            $fieldErrors = Validator::validateFields([
                "name" => ValueType::STRING,
                "username" => ValueType::STRING,
            ], $_POST);

            if (
                !isset($fieldErrors["name"]) &&
                !Validator::validateStrLen($_POST["name"], User::$minLens["name"], User::$maxLens["name"])
            ) $fieldErrors["name"] = sprintf(
                "%s文字以上、%s文字以下で入力してください。",
                User::$minLens["name"],
                User::$maxLens["name"],
            );

            if (!isset($fieldErrors["username"])) {
                if (!Validator::validateStrLen($_POST["username"], User::$minLens["username"], User::$maxLens["username"])) {
                    $fieldErrors["username"] = sprintf(
                        "%s文字以上、%s文字以下で入力してください。",
                        User::$minLens["username"],
                        User::$maxLens["username"],
                    );
                } else {
                    $sameUsernameUser = $userDao->getByUsername($_POST["username"]);
                    if ($sameUsernameUser !== null && $sameUsernameUser->getUserId() !== $user->getUserId()) {
                        $fieldErrors["username"] = "このユーザー名は使用できません。";
                    }
                }
            }

            if (!isset($fieldErrors["profile-text"])) {
                if (!Validator::validateStrLen($_POST["profile-text"], User::$minLens["profile_text"], User::$maxLens["profile_text"])) {
                    $fieldErrors["profile-text"] = sprintf(
                        "%s文字以下で入力してください。",
                        User::$maxLens["profile_text"],
                    );
                }
            }

            $profileImageType = $_POST["profile-image-type"];
            $profileImageUploaded = $profileImageType === "custom" && $_FILES["profile-image"]["error"] === UPLOAD_ERR_OK;
            if ($profileImageUploaded) {
                if (!Validator::validateImageType($_FILES["profile-image"]["type"])) {
                    $fieldErrors["profile-image"] =
                        "ファイル形式が不適切です。JPG, JPEG, PNG, GIFのファイルが設定可能です。";
                } else if (!Validator::validateImageSize($_FILES["profile-image"]["size"])) {
                    $fieldErrors["profile-image"] =
                        "ファイルが大きすぎます。";
                }
            }

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // プロフィール画像を保存
            if ($profileImageUploaded) {
                $imageHash = ImageOperator::saveProfileImage(
                    $_FILES["profile-image"]["tmp_name"],
                    ImageOperator::imageTypeToExtension($_FILES["profile-image"]["type"]),
                    $user->getUsername(),
                );
            } else if ($profileImageType === "custom") {
                $imageHash = $user->getProfileImageHash();
            } else {
                $imageHash = null;
            }

            // 元のプロフィール画像が不要になる場合は削除
            if ($user->getProfileImageHash() !== null) {
                if ($profileImageUploaded || $profileImageType === "default") {
                    ImageOperator::deleteProfileImage($user->getProfileImageHash());
                }
            }

            // ユーザーのデータを更新
            $user->setName($_POST["name"]);
            $user->setUsername($_POST["username"]);
            $user->setProfileText($_POST["profile-text"]);
            $user->setProfileImageHash($imageHash);
            $userDao->update($user);

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
];

<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticator;
use Helpers\DateOperator;
use Helpers\Hasher;
use Helpers\ImageOperator;
use Helpers\MailSender;
use Helpers\Validator;
use Models\Follow;
use Models\Like;
use Models\Notification;
use Models\Post;
use Models\TempUser;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;
use Routing\Route;
use Types\ValueType;

require_once(sprintf("%s/../constants/file_constants.php", __DIR__));

return [
    // ユーザー認証関連
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
            $resBody["redirectUrl"] = "/login";
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth"]),

    // ユーザープロフィール関連
    "/api/user/profile/init" => Route::create("/api/user/profile/init", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            if ($user === null) {
                $resBody["userData"] = null;
            } else {
                $followDao = DAOFactory::getFollowDAO();

                $userData = [
                    "isLoggedInUser" => intval($user->getUsername() === $authenticatedUser->getUsername()),
                    "isFollowee" => intval($followDao->isFollowee($authenticatedUser->getUserId(), $user->getUserId())),
                    "isFollower" => intval($followDao->isFollower($authenticatedUser->getUserId(), $user->getUserId())),
                    "name" => $user->getName(),
                    "username" => $user->getUsername(),
                    "profileText" => $user->getProfileText(),
                    "profileImagePath" => $user->getProfileImageHash() ?
                        PROFILE_IMAGE_FILE_DIR . $user->getProfileImageHash() :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profileImageType" => $user->getProfileImageHash() === null ? "default" : "custom",
                    "followeeCount" => $followDao->getFolloweeCount($user->getUserId()),
                    "followerCount" => $followDao->getFollowerCount($user->getUserId()),
                ];
                $resBody["userData"] = $userData;
            }

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
    "/api/user/follow" => Route::create("/api/user/follow", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $username = $_POST["username"];
            if ($username === "") {
                throw new Exception("パラメータが不適切です。");
            }

            $userDao = DAOFactory::getUserDAO();
            $user = $userDao->getByUsername($username);
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($user === null) {
                throw new Exception("フォロー対象のユーザーが存在しません。");
            } else if ($user->getUserId() === $authenticatedUser->getUserId()) {
                throw new Exception("フォロー対象のユーザーが不適切です。");
            }

            $followDao = DAOFactory::getFollowDAO();
            $isFollowee = $followDao->isFollowee($authenticatedUser->getUserId(), $user->getUserId());
            if ($isFollowee) {
                throw new Exception("既にフォローしています。");
            }

            $follow = new Follow(
                follower_id: $authenticatedUser->getUserId(),
                followee_id: $user->getUserId(),
            );
            $result = $followDao->follow($follow);
            if (!$result) {
                throw new Exception("フォロー処理に失敗しました。");
            }

            $notification = new Notification(
                from_user_id: $authenticatedUser->getUserId(),
                to_user_id: $user->getUserId(),
                type: "FOLLOW",
            );
            $notificationDao = DAOFactory::getNotificationDAO();
            $result = $notificationDao->create($notification);
            if (!$result) {
                throw new Exception("通知作成処理に失敗しました。");
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/unfollow" => Route::create("/api/user/unfollow", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $username = $_POST["username"];
            if ($username === "") {
                throw new Exception("パラメータが不適切です。");
            }

            $userDao = DAOFactory::getUserDAO();
            $user = $userDao->getByUsername($username);
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($user === null) {
                throw new Exception("アンフォロー対象のユーザーが存在しません。");
            } else if ($user->getUserId() === $authenticatedUser->getUserId()) {
                throw new Exception("アンフォロー対象のユーザーが不適切です。");
            }

            $followDao = DAOFactory::getFollowDAO();
            $isFollowee = $followDao->isFollowee($authenticatedUser->getUserId(), $user->getUserId());
            if (!$isFollowee) {
                throw new Exception("現在フォローしているユーザーではありません。");
            }

            $result = $followDao->unfollow($authenticatedUser->getUserId(), $user->getUserId());
            if (!$result) {
                throw new Exception("アンフォロー処理に失敗しました。");
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/followers" => Route::create("/api/user/followers", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            if ($user === null) {
                $resBody["followers"] = null;
            } else {
                $followDao = DAOFactory::getFollowDAO();

                $limit = $_POST["limit"] ?? 30;
                $offset = $_POST["offset"] ?? 0;
                $followers = $followDao->getFollowers($user->getUserId(), $limit, $offset);

                for ($i = 0; $i < count($followers); $i++) {
                    $followers[$i] = [
                        "name" => $followers[$i]["name"],
                        "username" => $followers[$i]["username"],
                        "profileImagePath" => $followers[$i]["profile_image_hash"] ?
                            PROFILE_IMAGE_FILE_DIR . $followers[$i]["profile_image_hash"] :
                            PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                        "profilePath" => "/user?un=" . $followers[$i]["username"],
                    ];
                }

                $resBody["followers"] = $followers;
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/followees" => Route::create("/api/user/followees", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            if ($user === null) {
                $resBody["followees"] = null;
            } else {
                $followDao = DAOFactory::getFollowDAO();

                $limit = $_POST["limit"] ?? 30;
                $offset = $_POST["offset"] ?? 0;
                $followees = $followDao->getFollowees($user->getUserId(), $limit, $offset);

                for ($i = 0; $i < count($followees); $i++) {
                    $followees[$i] = [
                        "name" => $followees[$i]["name"],
                        "username" => $followees[$i]["username"],
                        "profileImagePath" => $followees[$i]["profile_image_hash"] ?
                            PROFILE_IMAGE_FILE_DIR . $followees[$i]["profile_image_hash"] :
                            PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                        "profilePath" => "/user?un=" . $followees[$i]["username"],
                    ];
                }

                $resBody["followees"] = $followees;
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/posts" => Route::create("/api/user/posts", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postDao = DAOFactory::getPostDAO();
            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $user->getUserId();
            $posts = $postDao->getUserPosts($userId, $limit, $offset);

            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i] = [
                    "postId" => $posts[$i]["post_id"],
                    "content" => $posts[$i]["content"],
                    "imagePath" => $posts[$i]["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "thumbnailPath" => $posts[$i]["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $posts[$i]["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($posts[$i]["updated_at"]),
                    "replyCount" => $posts[$i]["reply_count"],
                    "likeCount" => $posts[$i]["like_count"],
                    "liked" => $posts[$i]["liked"],
                    "name" => $posts[$i]["name"],
                    "username" => $posts[$i]["username"],
                    "profileImagePath" => $posts[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $posts[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $posts[$i]["username"],
                    "deletable" => $authenticatedUser->getUsername() === $posts[$i]["username"],
                ];
            }

            $resBody["posts"] = $posts;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/replies" => Route::create("/api/user/replies", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postDao = DAOFactory::getPostDAO();
            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $user->getUserId();
            $posts = $postDao->getUserReplies($userId, $limit, $offset);

            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i] = [
                    "postId" => $posts[$i]["post_id"],
                    "content" => $posts[$i]["content"],
                    "imagePath" => $posts[$i]["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "thumbnailPath" => $posts[$i]["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $posts[$i]["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($posts[$i]["updated_at"]),
                    "replyCount" => $posts[$i]["reply_count"],
                    "likeCount" => $posts[$i]["like_count"],
                    "liked" => $posts[$i]["liked"],
                    "name" => $posts[$i]["name"],
                    "username" => $posts[$i]["username"],
                    "profileImagePath" => $posts[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $posts[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $posts[$i]["username"],
                    "deletable" => $authenticatedUser->getUsername() === $posts[$i]["username"],
                ];
            }

            $resBody["posts"] = $posts;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/user/likes" => Route::create("/api/user/likes", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postDao = DAOFactory::getPostDAO();
            $username = $_POST["username"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            if ($username === "") {
                $user = Authenticator::getAuthenticatedUser();
            } else {
                $userDao = DAOFactory::getUserDAO();
                $user = $userDao->getByUsername($username);
            }

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $user->getUserId();
            $posts = $postDao->getUserLikes($userId, $limit, $offset);

            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i] = [
                    "postId" => $posts[$i]["post_id"],
                    "content" => $posts[$i]["content"],
                    "imagePath" => $posts[$i]["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "thumbnailPath" => $posts[$i]["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $posts[$i]["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($posts[$i]["updated_at"]),
                    "replyCount" => $posts[$i]["reply_count"],
                    "likeCount" => $posts[$i]["like_count"],
                    "liked" => $posts[$i]["liked"],
                    "name" => $posts[$i]["name"],
                    "username" => $posts[$i]["username"],
                    "profileImagePath" => $posts[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $posts[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $posts[$i]["username"],
                    "deletable" => $authenticatedUser->getUsername() === $posts[$i]["username"],
                ];
            }

            $resBody["posts"] = $posts;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),

    // タイムライン関連
    "/api/timeline/trend" => Route::create("/api/timeline/trend", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $postDao = DAOFactory::getPostDAO();

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $authenticatedUser->getUserId();
            $posts = $postDao->getTrendTimelinePosts($userId, $limit, $offset);

            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i] = [
                    "postId" => $posts[$i]["post_id"],
                    "content" => $posts[$i]["content"],
                    "imagePath" => $posts[$i]["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "thumbnailPath" => $posts[$i]["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $posts[$i]["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($posts[$i]["updated_at"]),
                    "replyCount" => $posts[$i]["reply_count"],
                    "likeCount" => $posts[$i]["like_count"],
                    "liked" => $posts[$i]["liked"],
                    "name" => $posts[$i]["name"],
                    "username" => $posts[$i]["username"],
                    "profileImagePath" => $posts[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $posts[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $posts[$i]["username"],
                    "deletable" => $authenticatedUser->getUsername() === $posts[$i]["username"],
                ];
            }

            $resBody["posts"] = $posts;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/timeline/follow" => Route::create("/api/timeline/follow", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $postDao = DAOFactory::getPostDAO();

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $authenticatedUser->getUserId();
            $posts = $postDao->getFollowTimelinePosts($userId, $limit, $offset);

            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i] = [
                    "postId" => $posts[$i]["post_id"],
                    "content" => $posts[$i]["content"],
                    "imagePath" => $posts[$i]["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "thumbnailPath" => $posts[$i]["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $posts[$i]["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $posts[$i]["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($posts[$i]["updated_at"]),
                    "replyCount" => $posts[$i]["reply_count"],
                    "likeCount" => $posts[$i]["like_count"],
                    "liked" => $posts[$i]["liked"],
                    "name" => $posts[$i]["name"],
                    "username" => $posts[$i]["username"],
                    "profileImagePath" => $posts[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $posts[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $posts[$i]["username"],
                    "deletable" => $authenticatedUser->getUsername() === $posts[$i]["username"],
                ];
            }

            $resBody["posts"] = $posts;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = $e->getMessage();
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),

    // ポスト関連
    "/api/post/create" => Route::create("api/post/create", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $user = Authenticator::getAuthenticatedUser();
            $postDao = DAOFactory::getPostDAO();

            // 入力値検証
            if (!in_array($_POST["type"], ["create", "draft", "schedule"])) {
                throw new Exception("リクエストデータが不適切です。");
            }

            $fieldErrors = Validator::validateFields([
                "post-content" => ValueType::STRING,
            ], $_POST);

            if (
                !isset($fieldErrors["post-content"]) &&
                !Validator::validateStrLen($_POST["post-content"], Post::$minLens["content"], Post::$maxLens["content"])
            ) $fieldErrors["post-content"] = sprintf(
                "%s文字以上、%s文字以下で入力してください。",
                User::$minLens["content"],
                User::$maxLens["content"],
            );

            $postImageUploaded = $_FILES["post-image"]["error"] === UPLOAD_ERR_OK;
            if ($postImageUploaded) {
                if (!Validator::validateImageType($_FILES["post-image"]["type"])) {
                    $fieldErrors["post-image"] =
                        "ファイル形式が不適切です。JPG, JPEG, PNG, GIFのファイルが設定可能です。";
                } else if (!Validator::validateImageSize($_FILES["post-image"]["size"])) {
                    $fieldErrors["post-image"] =
                        "ファイルが大きすぎます。";
                }
            }

            if ($_POST["type"] === "schedule") {
                if ($_POST["post-scheduled-at"] === null || !Validator::validateDateTime($_POST["post-scheduled-at"])) {
                    $fieldErrors["post-scheduled-at"] =
                        "日付を正しく設定してください。";
                }
            }

            // 入力値検証でエラーが存在すれば、そのエラー情報をレスポンスとして返す
            if (!empty($fieldErrors)) {
                $resBody["success"] = false;
                $resBody["fieldErrors"] = $fieldErrors;
                return new JSONRenderer($resBody);
            }

            // 画像を保存
            if ($postImageUploaded) {
                $imageHash = ImageOperator::savePostImage(
                    $_FILES["post-image"]["tmp_name"],
                    ImageOperator::imageTypeToExtension($_FILES["post-image"]["type"]),
                    $user->getUsername(),
                );
            }

            // 返信ポストかどうかを判定
            $isReply = intval($_POST["post-reply-to-id"] ?? "0") !== 0;
            if ($isReply) {
                $parentPost = $postDao->getPostById($_POST["post-reply-to-id"]);
                if ($parentPost === null) {
                    throw new Exception("返信先のポストが存在しません。");
                }
            }

            // 新しいPostオブジェクトを作成
            $status = "POSTED";
            if ($_POST["type"] === "draft") $status = "SAVED";
            else if ($_POST["type"] === "schedule") $status = "SCHEDULED";

            $post = new Post(
                content: $_POST["post-content"],
                status: $status,
                user_id: $user->getUserId(),
                reply_to_id: $isReply ? $parentPost->getPostId() : null,
            );
            if ($postImageUploaded) $post->setImageHash($imageHash);
            if ($status === "SCHEDULED") $post->setScheduledAt($_POST["post-scheduled-at"]);

            // ポストを作成
            $success = $postDao->create($post);
            if (!$success) throw new Exception("ポスト作成に失敗しました。");

            // 通知を作成
            if ($isReply) {
                $notification = new Notification(
                    from_user_id: $user->getUserId(),
                    to_user_id: $parentPost->getUserId(),
                    source_id: $parentPost->getPostId(),
                    type: "REPLY",
                );
                $notificationDao = DAOFactory::getNotificationDAO();
                $result = $notificationDao->create($notification);
                if (!$result) {
                    throw new Exception("通知作成処理に失敗しました。");
                }
            }

            $message = $isReply ? "返信しました。" : "ポストを作成しました。";
            if ($status === "SAVED") $message = "ポストを下書きに保存しました。";
            if ($status === "SCHEDULED") $message = "ポストを予約しました。";
            FlashData::setFlashData("success", $message);

            if ($isReply) $resBody["redirectUrl"] = "/post?id=" . $_POST["post-reply-to-id"];
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/post/delete" => Route::create("/api/post/delete", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postDao = DAOFactory::getPostDAO();
            $postId = $_POST["post_id"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            $post = $postDao->getPost($postId, $authenticatedUser->getUserId());
            if ($post["username"] !== $authenticatedUser->getUsername()) throw new Exception("このポストは削除できません。");

            $postDao->delete($postId);
            if ($post["image_hash"]) {
                ImageOperator::deletePostImage($post["image_hash"]);
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/post/detail" => Route::create("/api/post/detail", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postId = $_POST["postId"];

            if ($postId === null) throw new Exception("リクエストデータが不適切です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $postDao = DAOFactory::getPostDAO();
            $post = $postDao->getPost($postId, $authenticatedUser->getUserId());

            if ($post === null) {
                $resBody["post"] = null;
            } else {
                $resBody["post"] = [
                    "postId" => $post["post_id"],
                    "content" => $post["content"],
                    "imagePath" => $post["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $post["image_hash"] :
                        "",
                    "thumbnailPath" => $post["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $post["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $post["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($post["updated_at"]),
                    "replyCount" => $post["reply_count"],
                    "likeCount" => $post["like_count"],
                    "liked" => $post["liked"],
                    "name" => $post["name"],
                    "username" => $post["username"],
                    "profileImagePath" => $post["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $post["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $post["username"],
                    "deletable" => $authenticatedUser->getUsername() === $post["username"],
                ];
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/post/replies" => Route::create("/api/post/replies", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $postId = $_POST["postId"];
            $replyLimit = $_POST["replyLimit"];
            $replyOffset = $_POST["replyOffset"];

            if ($postId === null) throw new Exception("リクエストデータが不適切です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $postDao = DAOFactory::getPostDAO();
            $replies = $postDao->getReplies($postId, $authenticatedUser->getUserId(), $replyLimit, $replyOffset);

            $resBody["replies"] = array_map(function($post) use ($authenticatedUser) {
                return [
                    "postId" => $post["post_id"],
                    "content" => $post["content"],
                    "imagePath" => $post["image_hash"] ?
                        POST_ORIGINAL_IMAGE_FILE_DIR . $post["image_hash"] :
                        "",
                    "thumbnailPath" => $post["image_hash"] ?
                        POST_THUMBNAIL_IMAGE_FILE_DIR . $post["image_hash"] :
                        "",
                    "postPath" => "/post?id=" . $post["post_id"],
                    "postedAt" => DateOperator::getTimeDiff($post["updated_at"]),
                    "replyCount" => $post["reply_count"],
                    "likeCount" => $post["like_count"],
                    "liked" => $post["liked"],
                    "name" => $post["name"],
                    "username" => $post["username"],
                    "profileImagePath" => $post["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $post["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "profilePath" => "/user?un=" . $post["username"],
                    "deletable" => $authenticatedUser->getUsername() === $post["username"],
                ];
            }, $replies);

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/post/like" => Route::create("/api/post/like", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $likeDao = DAOFactory::getLikeDAO();
            $postDao = DAOFactory::getPostDAO();

            $postId = $_POST["post_id"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            $exists = $likeDao->exists($authenticatedUser->getUserId(), $postId);
            if ($exists) throw new Exception("既にいいねしています。");

            $post = $postDao->getPostById($postId);
            if ($post === null) throw new Exception("いいねするポストが存在しません。");

            $like = new Like(
                user_id: $authenticatedUser->getUserId(),
                post_id: $post->getPostId(),
            );
            $likeDao->like($like);

            $notification = new Notification(
                from_user_id: $authenticatedUser->getUserId(),
                to_user_id: $post->getUserId(),
                source_id: $post->getPostId(),
                type: "LIKE",
            );
            $notificationDao = DAOFactory::getNotificationDAO();
            $result = $notificationDao->create($notification);
            if (!$result) {
                throw new Exception("通知作成処理に失敗しました。");
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/post/unlike" => Route::create("/api/post/unlike", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $likeDao = DAOFactory::getLikeDAO();
            $postId = $_POST["post_id"];
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            $exists = $likeDao->exists($authenticatedUser->getUserId(), $postId);
            if (!$exists) throw new Exception("既にいいねされていません。");

            $likeDao->unlike($authenticatedUser->getUserId(), $postId);

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),

    // 通知関連
    "/api/notifications/list" => Route::create("/api/notifications/list", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            $notificationDao = DAOFactory::getNotificationDAO();

            $limit = $_POST["limit"] ?? 30;
            $offset = $_POST["offset"] ?? 0;
            $userId = $authenticatedUser->getUserId();
            $notifications = $notificationDao->getUserNotifications($userId, $limit, $offset);

            // "SELECT n.type, n.source_id, n.is_read, fu.name, fu.username, fu.profile_image_hash " .
            for ($i = 0; $i < count($notifications); $i++) {
                if ($notifications[$i]["type"] === "LIKE") $notificationPath = "/post?id=" . $notifications[$i]["source_id"];
                else if ($notifications[$i]["type"] === "FOLLOW") $notificationPath = "/user?un=" . $notifications[$i]["username"];
                else if ($notifications[$i]["type"] === "REPLY") $notificationPath = "/post?id=" . $notifications[$i]["source_id"];
                else $notificationPath = "/post?id=" . $notifications[$i]["source_id"];

                $notifications[$i] = [
                    "notificationId" => $notifications[$i]["notification_id"],
                    "notificationPath" => $notificationPath,
                    "type" => $notifications[$i]["type"],
                    "isRead" => $notifications[$i]["is_read"],
                    "fromUserName" => $notifications[$i]["name"],
                    "fromUserProfileImagePath" => $notifications[$i]["profile_image_hash"] ?
                        PROFILE_IMAGE_FILE_DIR . $notifications[$i]["profile_image_hash"] :
                        PROFILE_IMAGE_FILE_DIR . "default_profile_image.png",
                    "fromUserProfilePath" => "/user?un=" . $notifications[$i]["username"],
                ];
            }

            $resBody["notifications"] = $notifications;
            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
    "/api/notifications/read" => Route::create("/api/notifications/read", function(): HTTPRenderer {
        $resBody = ["success" => true];

        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("リクエストメソッドが不適切です。");

            $notificationId = $_POST["notification_id"];
            $notificationDao = DAOFactory::getNotificationDAO();
            $notification = $notificationDao->getNotificationById($notificationId);
            if ($notification === null) throw new Exception("無効な通知です。");

            $authenticatedUser = Authenticator::getAuthenticatedUser();
            if ($notification->getToUserId() !== $authenticatedUser->getUserId()) throw new Exception("通知に紐づくユーザーではありません。");

            $notification->setIsRead(true);
            $result = $notificationDao->updateIsRead($notification);
            if (!$result) {
                throw new Exception("通知確認ステータス更新処理に失敗しました。");
            }

            return new JSONRenderer($resBody);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resBody["success"] = false;
            $resBody["error"] = "エラーが発生しました。";
            return new JSONRenderer($resBody);
        }
    })->setMiddleware(["api_auth", "api_email_verified"]),
];

<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Helpers\Settings;

class MailSend {
    public static function sendVerificationMail(string $signedURL, string $toAddress, string $toName, string $mailType): bool {
        $mail = new PHPMailer(true);

        try {
            // サーバの設定
            $mail->isSMTP(); // SMTPを使用するようにメーラーを設定
            $mail->Host = "smtp.gmail.com"; // GmailのSMTPサーバ
            $mail->SMTPAuth = true; // SMTP認証を有効化
            $mail->Username = Settings::env("SMPT_USER"); // SMTPユーザー名
            $mail->Password = Settings::env("SMPT_PASSWORD"); // SMTPパスワード
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS暗号化を有効化
            $mail->Port = 587; // 接続先のTCPポート
            $mail->CharSet = "UTF-8"; // 文字コードをUTF-8に設定

            $mail->setFrom(Settings::env("MAIL_FROM_ADDRESS"), Settings::env("MAIL_FROM_NAME")); // 送信者を設定
            $mail->addAddress($toAddress, $toName); // 受信者を追加

            $mail->isHTML(); // メール形式をHTMLに設定

            // 本文, 件名を設定
            switch ($mailType) {
                case "email_verification":
                    ob_start();
                    extract([
                        "signedURL" => $signedURL,
                        "toName" => $toName,
                    ]);
                    include("../views/mail/email_verify.php");
                    $mail->Body = ob_get_clean();
                    $textBody = file_get_contents("../views/mail/email_verify.txt");
                    $mail->Subject = "[SNS] メールアドレスを確認してください。";
                    break;

                case "password_reset":
                    ob_start();
                    extract([
                        "signedURL" => $signedURL,
                        "toName" => $toName,
                    ]);
                    include("../views/mail/password_reset.php");
                    $mail->Body = ob_get_clean();
                    $textBody = file_get_contents("../views/mail/password_reset.txt");
                    $mail->Subject = "[SNS] パスワードを変更してください。";
                    break;
            }

            $textBody = str_replace("[URL]", $signedURL, $textBody);
            $textBody = str_replace("[TO_NAME]", $toName, $textBody);
            $mail->AltBody = $textBody;

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}

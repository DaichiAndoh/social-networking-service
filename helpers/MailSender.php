<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Helpers\ConfigReader;

class MailSender {
    public static function sendEmailVerificationMail(string $signedURL, string $toAddress, string $toName): bool {
        $mail = new PHPMailer(true);

        try {
            // サーバの設定
            $mail->isSMTP(); // SMTPを使用するようにメーラーを設定
            $mail->Host = "smtp.gmail.com"; // GmailのSMTPサーバ
            $mail->SMTPAuth = true; // SMTP認証を有効化
            $mail->Username = ConfigReader::env("SMPT_USER"); // SMTPユーザー名
            $mail->Password = ConfigReader::env("SMPT_PASSWORD"); // SMTPパスワード
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS暗号化を有効化
            $mail->Port = 587; // 接続先のTCPポート
            $mail->CharSet = "UTF-8"; // 文字コードをUTF-8に設定

            $mail->setFrom(ConfigReader::env("MAIL_FROM_ADDRESS"), ConfigReader::env("MAIL_FROM_NAME")); // 送信者を設定
            $mail->addAddress($toAddress, $toName); // 受信者を追加

            $mail->Subject = "[SNS] メールアドレスを確認してください。"; // メール件名を設定

            $mail->isHTML(); // メール形式をHTMLに設定

            // メール本文を設定
            ob_start();
            extract([
                "signedURL" => $signedURL,
                "toName" => $toName,
            ]);
            include("../views/mail/email_verification.php");
            $mail->Body = ob_get_clean();

            $textBody = file_get_contents("../views/mail/email_verification.txt");
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

    public static function sendPasswordResetMail(string $signedURL, string $toAddress, string $toName): bool {
        $mail = new PHPMailer(true);

        try {
            // サーバの設定
            $mail->isSMTP(); // SMTPを使用するようにメーラーを設定
            $mail->Host = "smtp.gmail.com"; // GmailのSMTPサーバ
            $mail->SMTPAuth = true; // SMTP認証を有効化
            $mail->Username = ConfigReader::env("SMPT_USER"); // SMTPユーザー名
            $mail->Password = ConfigReader::env("SMPT_PASSWORD"); // SMTPパスワード
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS暗号化を有効化
            $mail->Port = 587; // 接続先のTCPポート
            $mail->CharSet = "UTF-8"; // 文字コードをUTF-8に設定

            $mail->setFrom(ConfigReader::env("MAIL_FROM_ADDRESS"), ConfigReader::env("MAIL_FROM_NAME")); // 送信者を設定
            $mail->addAddress($toAddress, $toName); // 受信者を追加

            $mail->Subject = "[SNS] パスワードを変更してください。"; // メール件名を設定

            $mail->isHTML(); // メール形式をHTMLに設定

            // メール本文を設定
            ob_start();
            extract([
                "signedURL" => $signedURL,
                "toName" => $toName,
            ]);
            include("../views/mail/password_reset.php");
            $mail->Body = ob_get_clean();

            $textBody = file_get_contents("../views/mail/password_reset.txt");
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

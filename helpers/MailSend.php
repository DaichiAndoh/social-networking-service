<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Helpers\Settings;

class MailSend {
    public static function sendVerificationMail(string $signedURL, string $subject, string $toAddress, string $toName): bool {
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

            $mail->Subject = $subject; // 件名を設定

            $mail->isHTML(); // メール形式をHTMLに設定

            // 本文を設定
            ob_start();
            extract([
                "signedURL" => $signedURL,
                "toName" => $toName,
            ]);
            include("../views/mail/email_verify.php");
            $mail->Body = ob_get_clean();

            $textBody = file_get_contents("../views/mail/email_verify.txt");
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

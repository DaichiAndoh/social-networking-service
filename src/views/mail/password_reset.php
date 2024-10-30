<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>パスワード変更</title>
</head>
<body>
    <p><?php echo htmlspecialchars($toName) . " さま"; ?></p>
    <p>下のURLをクリックして、パスワードを変更してください。</p>
    <p><a href="<?php echo htmlspecialchars($signedURL); ?>" style="color: #1a73e8;">パスワードを変更する</a></p>
    <p>このURLの有効期間は1時間です。</p>
    <p>このメールに心当たりがない場合は、お手数ですがこのメールを削除してください。</p>
</body>
</html>

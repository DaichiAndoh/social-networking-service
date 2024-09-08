<?php
$success = \Response\FlashData::getFlashData("success");
$error = \Response\FlashData::getFlashData("error");
?>

<div>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
</div>

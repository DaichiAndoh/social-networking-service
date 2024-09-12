<?php
$success = \Response\FlashData::getFlashData("success");
$error = \Response\FlashData::getFlashData("error");
?>

<div id="alert" class="position-fixed top-0 start-0 end-0">
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

<script src="/js/alert.js"></script>

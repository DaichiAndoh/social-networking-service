<div class="container-fluid pt-5" style="background-color: #051c2c; min-height: 100vh;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card py-5 px-3 my-3">
                <h2 class="text-center mb-4">パスワードリセット</h2>
                <form id="password-reset-form" method="post">
                <input type="hidden" name="signature" value="<?= $signature; ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">新しいパスワード</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="password-error-msg" class="invalid-feedback"></div>
                        <div class="form-text">
                            <p class="m-0">以下の条件を満たすパスワードを設定してください。</p>
                            <p class="m-0 ms-2">- 8文字以上30文字以下</p>
                            <p class="m-0 ms-2">- 1文字以上の大文字</p>
                            <p class="m-0 ms-2">- 1文字以上の小文字</p>
                            <p class="m-0 ms-2">- 1文字以上の数値</p>
                            <p class="m-0 ms-2">- 1文字以上の特殊文字（アルファベット以外の文字）</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">パスワード（確認）</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                        <div id="confirm-password-error-msg" class="invalid-feedback"></div>
                    </div>
                    <div class="text-center mt-5">
                        <button id="password-reset-btn" type="submit" class="btn btn-primary w-100">
                            パスワードリセット
                            <div id="password-reset-btn-spinner" class="spinner-border spinner-border-sm text-light d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/js/page/password_reset.js"></script>

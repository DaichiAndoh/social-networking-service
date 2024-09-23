<div class="container-fluid pt-5" style="background-color: #051c2c; min-height: 100vh;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card py-5 px-3 my-3">
                <h2 class="text-center mb-4">パスワードをお忘れの場合</h2>
                <form id="password-forgot-form" method="post">
                    <p class="text-center mb-0">登録したメールアドレスを入力してください。</p>
                    <p class="text-center">このメールアドレス宛にパスワード変更用リンクを送信します。</p>

                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div id="email-error-msg" class="invalid-feedback"></div>
                    </div>
                    <div class="text-center mt-5">
                        <button id="password-forgot-btn" type="submit" class="btn btn-primary w-100">
                            送信
                            <div id="btn-spinner" class="spinner-border spinner-border-sm text-light d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>

                <div id="completion-msg" class="text-center d-none">
                    <h4>送信完了</h4>
                    <p class="m-0 mt-4">登録されたメールアドレス宛にパスワード変更用メールを送信しました。</p>
                    <p class="mb-0">そちらのメールに従ってパスワード変更を完了させてください。</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/page/authentication/password_forgot.js"></script>

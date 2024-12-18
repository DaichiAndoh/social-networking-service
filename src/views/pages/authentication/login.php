<div class="container-fluid pt-5" style="background-color: #051c2c; min-height: 100vh;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card pt-3 pb-5 px-3 my-3">
                <div>
                    <a href="/" class="text-dark">
                        <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
                    </a>
                </div>

                <h2 class="text-center mb-4">ログイン</h2>
                <form id="login-form" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div id="email-error-msg" class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="password-error-msg" class="invalid-feedback"></div>
                    </div>
                    <div class="text-center mt-5">
                        <button id="login-btn" type="submit" class="btn btn-primary w-100">
                            ログイン
                            <div id="btn-spinner" class="spinner-border spinner-border-sm text-light d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>

                        <p class="m-0 mt-4"><a href="/password/forgot">パスワードをお忘れですか？</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/js/pages/authentication/login.js"></script>

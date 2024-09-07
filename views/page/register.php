<div class="container-fluid vh-100 pt-5" style="background-color: #051c2c;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card py-5 px-3">
                <h2 class="text-center mb-4">ユーザー登録</h2>
                <form action="/form/register" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">名前</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">ユーザー名</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">パスワード（確認）</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                    </div>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary w-100">ユーザー登録</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

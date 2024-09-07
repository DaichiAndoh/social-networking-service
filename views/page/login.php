<div class="container-fluid vh-100 pt-5" style="background-color: #051c2c;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card py-5 px-3">
                <h2 class="text-center mb-4">ログイン</h2>
                <form action="form/login" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary w-100">ログイン</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

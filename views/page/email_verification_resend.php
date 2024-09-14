<div class="container-fluid pt-5" style="background-color: #051c2c; min-height: 100vh;">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card py-5 px-3 my-3">
                <h2 class="text-center mb-4">メールアドレス検証</h2>
                <form id="email-verification-resend-form" method="post">
                    <p class="text-center mb-0">検証メールからメールアドレス検証を完了させてください。</p>
                    <p class="text-center">検証メールが届いていない、もしくはリンクの有効期限が切れている場合は、下のボタンより再送信することができます。</p>

                    <div class="text-center mt-5">
                        <button id="email-verification-resend-btn" type="submit" class="btn btn-primary w-100">
                            検証用メールを再送信する
                            <div id="btn-spinner" class="spinner-border spinner-border-sm text-light d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>

                <div id="completion-msg" class="text-center d-none">
                    <h4>送信完了</h4>
                    <p class="m-0 mt-4">検証用メールを送信しました。</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/page/email_verification_resend.js"></script>

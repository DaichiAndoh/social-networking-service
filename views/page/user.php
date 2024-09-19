<div class="bg-white p-3 my-0 mx-auto" style="max-width: 600px;">
    <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
        <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
    </a>

    <div id="user-not-found" class="py-3 text-center d-none">
        <h5>このアカウントは存在しません。</h5>
    </div>

    <div id="profile-block" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a id="profile-profile-image-link" href="" target="_blank" rel="noopener noreferrer">
                <img id="profile-profile-image" src="" alt="プロフィール画像" width="90" height="90" class="rounded-circle border">
            </a>
            <button id="profile-edit-btn" class="btn btn-primary btn-sm d-none" data-bs-toggle="modal" data-bs-target="#profileEditModal">プロフィールを編集</button>
            <button id="profile-follow-btn" class="btn btn-primary btn-sm d-none">フォロー</button>
            <button id="profile-unfollow-btn" class="btn btn-outline-primary btn-sm d-none">アンフォロー</button>
        </div>

        <div>
            <h5 id="profile-name" class="m-0"></h5>
            <p id="profile-username" class="m-0 text-secondary fw-light"></p>
            <p id="follower-label" class="m-0 text-secondary fw-light d-none">
                <small class="bg-light p-1 rounded">フォローされています</small>
            </p>
            <p id="profile-profile-text" class="mt-2"></p>
            <div class="d-block d-sm-flex gap-3">
                <a id="followee-link" class="mb-1 d-block text-black">フォロー <span id="followee-count"></span></a>
                <a id="follower-link" class="mb-1 d-block text-black">フォロワー <span id="follower-count"></span></a>
            </div>
        </div>
    </div>

    <div id="post-block" class="d-none">
        <ul class="nav nav-underline d-flex justify-content-center gap-sm-5">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">ポスト</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">リプライ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">いいね</a>
            </li>
        </ul>

        <div>
            <!-- ここにポスト一覧を表示する -->
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="profileEditModalLabel">プロフィール編集</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="profile-edit-form" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">名前</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div id="name-error-msg" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">ユーザー名</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div id="username-error-msg" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="profile-text" class="form-label">プロフィール</label>
                    <textarea class="form-control" id="profile-text" name="profile-text" rows="3"></textarea>
                    <div id="profile-text-error-msg" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">プロフィール画像</label>

                    <div class="mb-3">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="profile-image-type" id="profile-image-type-default" value="default">
                            <label class="form-check-label" for="profile-image-type-default">
                                デフォルト画像を使用
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="profile-image-type" id="profile-image-type-custom" value="custom">
                            <label class="form-check-label" for="profile-image-type-custom">
                                アップロードした画像を使用
                            </label>
                        </div>
                    </div>

                    <div id="profile-image-upload-block" class="d-flex align-items-center justify-content-between d-none">
                        <img id="profile-image-preview" src="" alt="プロフィール画像プレビュー" width="50" height="50" class="rounded-circle border">
                        <div class="flex-grow-1 ms-3">
                            <input type="file" class="form-control" id="profile-image" name="profile-image" accept=".jpg, .jpeg, .png, .gif">
                            <div id="profile-image-error-msg" class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 text-end">
                    <button id="save-btn" type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

</div>
</div>
</div>

<script src="/js/page/user.js"></script>

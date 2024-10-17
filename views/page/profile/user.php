<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <div>
        <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
            <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
        </a>
    </div>

    <div id="user-not-found" class="py-3 text-center d-none">
        <h5>このアカウントは存在しません。</h5>
    </div>

    <div id="profile-block" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a id="profile-profile-image-link" href="" target="_blank" rel="noopener noreferrer">
                <img id="profile-profile-image" src="" alt="プロフィール画像" width="90" height="90" class="rounded-circle border">
            </a>
            <div class="d-flex align-items-center gap-2">
                <a id="message-btn" class="p-1 rounded-circle border border-primary d-flex justify-content-center align-items-center d-none" style="width: 32px; height: 32px;">
                    <ion-icon name="mail-outline" class="fs-4"></ion-icon>
                </a>
                <button id="profile-edit-btn" class="btn btn-primary btn-sm d-none" data-bs-toggle="modal" data-bs-target="#profileEditModal">プロフィールを編集</button>
                <button id="profile-follow-btn" class="btn btn-primary btn-sm d-none">フォロー</button>
                <button id="profile-unfollow-btn" class="btn btn-outline-primary btn-sm d-none">アンフォロー</button>
            </div>
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

    <div id="post-block" class="d-none mt-3 flex-grow-1" style="height: 0;">
        <ul id="post-type-tabs"  class="nav nav-underline d-flex justify-content-center gap-sm-5">
            <li class="nav-item">
                <a id="posts-nav-link" class="nav-link active" href="#" data-target="#posts-list">ポスト</a>
            </li>
            <li class="nav-item">
                <a id="replies-nav-link" class="nav-link" href="#" data-target="#replies-list">リプライ</a>
            </li>
            <li class="nav-item">
                <a id="likes-nav-link" class="nav-link" href="#" data-target="#likes-list">いいね</a>
            </li>
        </ul>

        <div id="post-not-found" class="py-3 text-center d-none">
            <h6>ポストが存在しません。</h6>
        </div>

        <div id="list-wrapper" class="py-3 flex-grow-1" style="overflow-y: scroll;">
            <div id="posts-list" style="max-width: 500px; width: 100%; margin: 0 auto;">
            </div>

            <div id="replies-list" class="d-none" style="max-width: 500px; width: 100%; margin: 0 auto;">
            </div>

            <div id="likes-list" class="d-none" style="max-width: 500px; width: 100%; margin: 0 auto;">
            </div>

            <div id="spinner" class="text-center d-none my-2">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
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

<script src="/js/page/profile/user.js"></script>

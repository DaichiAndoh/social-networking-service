<?php if ($user !== null && $user->getEmailConfirmedAt() !== null): ?>
    <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPostModalLabel">新規ポスト</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-1">
                    <form method="post" id="create-post-form">
                        <ul class="nav nav-underline mb-2">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-target="#post-create-block">作成</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-target="#post-draft-block">下書き</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-target="#post-schedule-block">予約</a>
                            </li>
                        </ul>

                        <div id="post-create-block">
                            <div class="mb-3">
                                <label for="post-content" class="form-label">コンテンツ</label>
                                <textarea class="form-control" id="post-content" name="post-content" rows="3" required></textarea>
                                <div id="post-content-error-msg" class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="post-image" class="form-label">画像</label>
                                <input type="file" class="form-control" id="post-image" name="post-image" accept=".jpg, .jpeg, .png, .gif">
                                <div id="post-image-error-msg" class="invalid-feedback"></div>
                                <div id="post-image-preview-wrapper" class="d-none justify-content-center mt-3">
                                    <div class="text-center">
                                        <p class="p-0 m-0">選択された画像</p>
                                        <img id="post-image-preview" src="" alt="ポスト画像プレビュー" class="border" style="width: 100%; max-width: 150px;">
                                    </div>
                                    <ion-icon id="post-image-delete-icon" name="close-outline" class="fs-4" style="cursor: pointer;"></ion-icon>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="post-schedule">
                                <label class="form-check-label" for="post-schedule">予約する</label>
                            </div>
                            <div
                                id="post-datetimepicker"
                                class="input-group log-event d-none"
                                data-td-target-input="nearest"
                                data-td-target-toggle="nearest"
                            >
                                <input
                                    id="post-scheduled-at"
                                    type="text"
                                    class="form-control"
                                    data-td-target="#post-datetimepicker"
                                    name="post-scheduled-at"
                                />
                                <span
                                    class="input-group-text"
                                    data-td-target="#post-datetimepicker"
                                    data-td-toggle="datetimepicker"
                                >
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div id="post-scheduled-at-error-msg" class="invalid-feedback"></div>
                            </div>

                            <div class="mt-5 text-end">
                                <button id="post-draft-btn" type="submit" class="btn btn-secondary d-none">下書き保存</button>
                                <button id="post-create-btn" type="submit" class="btn btn-primary d-none">作成</button>
                                <button id="post-schedule-btn" type="submit" class="btn btn-primary d-none">予約</button>
                            </div>
                        </div>
                    </form>

                    <div id="post-draft-block" class="d-none">
                    </div>

                    <div id="post-schedule-block" class="d-none">
                        <div id="scheduled-post-not-exists" class="py-3 text-center d-none">
                            <h6>予約されているポストはありません。</h6>
                        </div>

                        <div id="scheduled-post-list-wrapper" style="max-height: 50vh; overflow-y: scroll;">
                            <div id="scheduled-post-list">
                            </div>

                            <div id="spinner" class="text-center d-none my-2">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>

                        <div id="scheduled-post-detail" class="d-none">
                            <div class="p-1">
                                <div class="text-dark">
                                    <ion-icon id="return-icon" name="arrow-back-outline" class="fs-4" style="cursor: pointer;"></ion-icon>
                                </div>
                                <div class="d-flex align-items-center gap-1 mb-1 text-secondary">
                                    <ion-icon name="calendar-outline"></ion-icon>
                                    <small id="detail-scheduled-at"></small>
                                </div>
                                <div id="detail-content" class="ms-1">
                                </div>
                                <div class="text-center">
                                    <a id="detail-image-link" class="d-none" href="#" target="_blank" rel="noopener noreferrer">
                                        <img id="detail-image" class="border" src="#" alt="ポスト画像">
                                    </a>
                                </div>
                                <div class="text-end">
                                    <button id="scheduled-post-delete-btn" type="button" class="btn btn-danger">削除</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

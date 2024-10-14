<?php if ($user !== null && $user->getEmailConfirmedAt() !== null): ?>
    <div class="modal fade" id="createReplyModal" tabindex="-1" aria-labelledby="createReplyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createReplyModalLabel">新規返信ポスト</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-1">
                    <form method="post" id="create-reply-form">
                        <ul class="nav nav-underline mb-2">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-target="#reply-create-block">作成</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-target="#reply-draft-block">下書き</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-target="#reply-schedule-block">予約</a>
                            </li>
                        </ul>

                        <div id="reply-create-block">
                            <input type="hidden" id="reply-to-id" name="post-reply-to-id" value="">
                            <div class="mb-3">
                                <label for="post-content" class="form-label">コンテンツ</label>
                                <textarea class="form-control" id="reply-content" name="post-content" rows="3" required></textarea>
                                <div id="reply-content-error-msg" class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="post-image" class="form-label">画像</label>
                                <input type="file" class="form-control" id="reply-image" name="post-image" accept=".jpg, .jpeg, .png, .gif">
                                <div id="reply-image-error-msg" class="invalid-feedback"></div>
                                <div id="reply-image-preview-wrapper" class="d-none justify-content-center mt-3">
                                    <div class="text-center">
                                        <p class="p-0 m-0">選択された画像</p>
                                        <img id="reply-image-preview" src="" alt="ポスト画像プレビュー" class="border" style="width: 100%; max-width: 150px;">
                                    </div>
                                    <ion-icon id="reply-image-delete-icon" name="close-outline" class="fs-4" style="cursor: pointer;"></ion-icon>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="reply-schedule">
                                <label class="form-check-label" for="reply-schedule">予約する</label>
                            </div>
                            <div
                                id="reply-datetimepicker"
                                class="input-group log-event d-none"
                                data-td-target-input="nearest"
                                data-td-target-toggle="nearest"
                            >
                                <input
                                    id="reply-scheduled-at"
                                    type="text"
                                    class="form-control"
                                    data-td-target="#reply-datetimepicker"
                                    name="post-scheduled-at"
                                />
                                <span
                                    class="input-group-text"
                                    data-td-target="#reply-datetimepicker"
                                    data-td-toggle="datetimepicker"
                                >
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div id="reply-scheduled-at-error-msg" class="invalid-feedback"></div>
                            </div>

                            <div class="mt-5 text-end">
                                <button id="reply-draft-btn" type="submit" class="btn btn-secondary d-none">下書き保存</button>
                                <button id="reply-create-btn" type="submit" class="btn btn-primary d-none">作成</button>
                                <button id="reply-schedule-btn" type="submit" class="btn btn-primary d-none">予約</button>
                            </div>
                        </div>

                        <div id="reply-draft-block" class="d-none">
                        </div>

                        <div id="reply-schedule-block" class="d-none">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

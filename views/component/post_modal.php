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
                                    id="post-datetimepicker-input"
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
                            </div>

                            <div class="mt-5 text-end">
                                <button id="post-draft-btn" type="submit" class="btn btn-secondary d-none">下書き保存</button>
                                <button id="post-create-btn" type="submit" class="btn btn-primary d-none">作成</button>
                                <button id="post-schedule-btn" type="submit" class="btn btn-primary d-none">予約</button>
                            </div>
                        </div>

                        <div id="post-draft-block" class="d-none">
                        </div>

                        <div id="post-schedule-block" class="d-none">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

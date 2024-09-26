<?php if ($user !== null && $user->getEmailConfirmedAt() !== null): ?>
    <!-- Modal -->
    <div class="modal fade" id="ceatePostModal" tabindex="-1" aria-labelledby="ceatePostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ceatePostModalLabel">新規ポスト</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <form method="post" id="create-post-form">
                    <ul class="nav nav-underline mb-2">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-target="#create-block">作成</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-target="#draft-block">下書き</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-target="#schedule-block">予約</a>
                        </li>
                    </ul>

                    <div id="create-block">
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
                            <input class="form-check-input" type="checkbox" role="switch" id="schedule">
                            <label class="form-check-label" for="schedule">予約する</label>
                        </div>
                        <div
                            id="datetimepicker"
                            class="input-group log-event d-none"
                            data-td-target-input="nearest"
                            data-td-target-toggle="nearest"
                        >
                            <input
                                id="datetimepicker-input"
                                type="text"
                                class="form-control"
                                data-td-target="#datetimepicker"
                                name="post-scheduled-at"
                            />
                            <span
                                class="input-group-text"
                                data-td-target="#datetimepicker"
                                data-td-toggle="datetimepicker"
                            >
                                <i class="fas fa-calendar"></i>
                            </span>
                        </div>

                        <div class="mt-5 text-end">
                            <button id="draft-btn" type="submit" class="btn btn-secondary d-none">下書き保存</button>
                            <button id="create-btn" type="submit" class="btn btn-primary d-none">作成</button>
                            <button id="schedule-btn" type="submit" class="btn btn-primary d-none">予約</button>
                        </div>
                    </div>

                    <div id="draft-block" class="d-none">
                    </div>

                    <div id="schedule-block" class="d-none">
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div id="sidebar" class="col-auto col-sm-4 col-md-3 col-xl-2 px-sm-1 px-0" style="background-color: #051c2c;">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-2 pt-2 min-vh-100">
                    <a id="logo" href="#" class="d-flex align-items-center me-md-auto text-light text-decoration-none">
                        <span class="fs-5 d-none d-sm-inline">SNS</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mt-0 mt-sm-3 align-items-center align-items-sm-start" id="menu">
                        <li class="nav-item mb-2">
                            <a href="/timeline" class="nav-link p-0 d-flex align-items-center text-light">
                                <ion-icon name="home-outline"></ion-icon>
                                <span class="fs-5 ms-2 d-none d-sm-inline">ホーム</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link p-0 d-flex align-items-center text-light">
                                <ion-icon name="notifications-outline"></ion-icon>
                                <span class="fs-5 ms-2 d-none d-sm-inline">通知</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link p-0 d-flex align-items-center text-light">
                                <ion-icon name="mail-outline"></ion-icon>
                                <span class="fs-5 ms-2 d-none d-sm-inline">メッセージ</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="/user" class="nav-link p-0 d-flex align-items-center text-light">
                                <ion-icon name="person-circle-outline"></ion-icon>
                                <span class="fs-5 ms-2 d-none d-sm-inline">プロフィール</span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <button id="post-btn" type="button" class="btn btn-primary d-none d-sm-flex justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#ceatePostModal">
                                <ion-icon name="add-outline"></ion-icon>
                                ポスト投稿
                            </button>
                            <button id="post-icon-btn" type="button" class="btn btn-primary d-flex d-sm-none justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#ceatePostModal">
                                <ion-icon name="add-outline"></ion-icon>
                            </button>
                        </li>
                    </ul>
                    <hr>
                    <div id="profile-dropdown" class="dropdown mb-4">
                        <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $profileImagePath ?>" alt="hugenerd" width="30" height="30" class="rounded-circle">
                            <span class="text-light d-none d-sm-inline mx-1">@<?= $user->getUsername() ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-light text-small shadow">
                            <li><a class="dropdown-item" href="/user">プロフィール</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a id="logout-link" class="dropdown-item">ログアウト</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="main-content" class="col bg-light text-dark" style="max-height: 100vh; overflow-y: hidden;">
                <!-- 下のコメントアウト部分を各ページファイルで作成する -->
                <!-- Content area...
            </div>
        </div>
    </div> -->
<?php endif; ?>

<?php if ($user !== null && $user->getEmailConfirmedAt() !== null): ?>
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
                            <button id="post-btn" type="button" class="btn btn-primary d-none d-sm-flex justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                <ion-icon name="add-outline"></ion-icon>
                                ポスト投稿
                            </button>
                            <button id="post-icon-btn" type="button" class="btn btn-primary d-flex d-sm-none justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#createPostModal">
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

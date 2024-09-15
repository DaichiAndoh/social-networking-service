<?php if ($user !== null && $user->getEmailConfirmedAt() !== null): ?>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-auto col-sm-4 col-md-3 col-xl-2 px-sm-1 px-0" style="background-color: #051c2c;">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-2 pt-2 min-vh-100">
                    <a id="logo" href="#" class="d-flex align-items-center me-md-auto text-light text-decoration-none">
                        <span class="fs-5 d-none d-sm-inline">SNS</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mt-0 mt-sm-3 align-items-center align-items-sm-start" id="menu">
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link p-0 d-flex align-items-center text-light">
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
                            <a href="#" class="nav-link p-0 d-flex align-items-center text-light">
                                <ion-icon name="person-circle-outline"></ion-icon>
                                <span class="fs-5 ms-2 d-none d-sm-inline">プロフィール</span>
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div id="profile-dropdown" class="dropdown mb-4">
                        <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://avatars.githubusercontent.com/u/160387631?s=96&v=4" alt="hugenerd" width="30" height="30" class="rounded-circle">
                            <span class="text-light d-none d-sm-inline mx-1">TesasdfasdfasdfasfdadsfasdftUser</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-light text-small shadow">
                            <li><a class="dropdown-item" href="#">設定</a></li>
                            <li><a class="dropdown-item" href="#">プロフィール</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">ログアウト</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col py-2 bg-light text-dark">
                <!-- 下のコメントアウト部分を各ページファイルで作成する -->
                <!-- Content area...
            </div>
        </div>
    </div> -->
<?php endif; ?>

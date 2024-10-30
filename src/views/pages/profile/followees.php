<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <div>
        <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
            <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
        </a>
    </div>

    <div id="user-not-found" class="py-3 text-center d-none">
        <h6>このアカウントは存在しません。</h6>
    </div>

    <div id="followees-not-exists" class="py-3 text-center d-none">
        <h6>現在フォローしているユーザーはいません。</h6>
    </div>

    <div id="list-wrapper" class="flex-grow-1" style="overflow-y: scroll;">
        <div id="followees-list">
        </div>

        <div id="spinner" class="text-center d-none my-2">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>


</div>
</div>
</div>

<script src="/js/pages/profile/followees.js"></script>

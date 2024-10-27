<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <div>
        <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
            <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
        </a>
    </div>

    <div id="post-not-found" class="py-3 text-center d-none">
        <h5>このポストは存在しません。</h5>
    </div>

    <div id="parent-post-block" class="d-none mt-0 mb-1 mx-auto" style="max-width: 500px; width: 100%;">
        <a id="toggle-parent-post-link" data-bs-toggle="collapse" href="#collapse-block" role="button" aria-expanded="false" aria-controls="collapse-block">
            <span id="link-text">返信元ポストを見る</span>
            <ion-icon id="link-icon" name="chevron-down-outline" style="font-size: 18px; vertical-align: text-top;"></ion-icon>
        </a>
        <div class="collapse mt-1" id="collapse-block">
            <div id="parent-post">
            </div>
            <ion-icon name="chevron-down-outline" style="font-size: 22px;"></ion-icon>
        </div>
    </div>

    <div id="post-block" class="d-none" style="max-width: 500px; width: 100%; margin: 0 auto;">
    </div>

    <div id="replies-block" class="d-none mt-3 flex-grow-1" style="max-width: 500px; width: 100%; margin: 0 auto;">
        <div style="width: 22px; height: 22px;">
            <ion-icon name="arrow-redo-outline" style="transform: scaleY(-1); font-size: 22px;"></ion-icon>
        </div>
        <div class="d-flex flex-column flex-grow-1">
            <div id="replies-wrapper" class="ms-2 py-3 pe-3 flex-grow-1" style="overflow-y: scroll; height: 0;">
                <div id="replies">
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
</div>
</div>

<script src="/js/page/post/detail.js"></script>

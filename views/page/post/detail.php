<div class="bg-white p-3 my-0 mx-auto" style="max-width: 600px;">
    <a href="javascript:void(0)" onclick="history.back()" class="text-dark">
        <ion-icon name="arrow-back-outline" class="fs-4"></ion-icon>
    </a>

    <div id="post-not-found" class="py-3 text-center d-none">
        <h5>このポストは存在しません。</h5>
    </div>

    <div id="post-block" class="d-none">
    </div>

    <div id="replies-block" class="d-none mt-3">
        <ion-icon name="arrow-redo-outline" class="fs-4" style="transform: scaleY(-1);"></ion-icon>
        <div id="replies-wrapper" class="w-100 ms-2 pb-3" style="overflow-y: scroll;">
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

<script src="/js/page/post/detail.js"></script>

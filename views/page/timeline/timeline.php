<div class="bg-white p-3 my-0 mx-auto d-flex flex-column" style="max-width: 600px; height: 100%;">
    <ul id="timeline-tabs" class="nav nav-underline d-flex justify-content-center gap-sm-5 mb-3">
        <li class="nav-item">
            <a id="trend-nav-link" class="nav-link active" href="#" data-target="#trend-timeline">トレンド</a>
        </li>
        <li class="nav-item">
            <a id="follow-nav-link" class="nav-link" href="#" data-target="#follow-timeline">フォロー</a>
        </li>
    </ul>

    <div id="timeline-wrapper" class="py-3 flex-grow-1" style="overflow-y: scroll;">
        <div id="trend-timeline" style="max-width: 500px; width: 100%; margin: 0 auto;">
        </div>

        <div id="follow-timeline" class="d-none" style="max-width: 500px; width: 100%; margin: 0 auto;">
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

<script src="/js/page/timeline/timeline.js"></script>

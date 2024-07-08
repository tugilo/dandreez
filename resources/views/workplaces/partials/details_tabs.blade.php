<ul class="nav nav-tabs" id="detailTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="instructions-tab" data-toggle="tab" href="#instructions" role="tab" aria-controls="instructions" aria-selected="true">施工指示</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">写真</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">ファイル</a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    @include('workplaces.partials.instructions_tab', ['instructions' => $instructions, 'role' => $role, 'storeRoute' => $storeRoute, 'updateRoute' => $updateRoute, 'destroyRoute' => $destroyRoute])
    @include('workplaces.partials.photos_tab', ['photos' => $photos, 'role' => $role, 'storeRoute' => $storeRoute, 'updateRoute' => $updateRoute, 'destroyRoute' => $destroyRoute])
    @include('workplaces.partials.files_tab', ['files' => $files, 'role' => $role, 'storeRoute' => $storeRoute, 'updateRoute' => $updateRoute, 'destroyRoute' => $destroyRoute])
</div>

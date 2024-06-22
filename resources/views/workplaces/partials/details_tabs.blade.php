<!-- resources/views/workplaces/partials/details_tabs.blade.php -->

<ul class="nav nav-tabs" id="detailTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="instructions-tab" data-toggle="tab" href="#instructions" role="tab" aria-controls="instructions" aria-selected="true">指示内容</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">写真</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">添付書類</a>
    </li>
</ul>

<div class="tab-content" id="detailTabsContent">
    @include('workplaces.partials.instructions_tab', ['workplace' => $workplace, 'units' => $units, 'instructions' => $instructions])
    @include('workplaces.partials.photos_tab', ['workplace' => $workplace, 'photos' => $photos])
    @include('workplaces.partials.files_tab', ['workplace' => $workplace, 'files' => $files])
</div>

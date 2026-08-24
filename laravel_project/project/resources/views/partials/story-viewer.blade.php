{{-- Paylaşılan Hikaye İzleyici (home + profil sayfalarında ortak kullanılır) --}}
@once
<div class="story-viewer" id="storyViewer" data-testid="story-viewer">
    <div class="story-viewer-backdrop" onclick="closeStoryViewer()"></div>
    <div class="story-viewer-stage">
        <div class="story-progress" id="storyProgress"></div>
        <div class="story-viewer-head">
            <div class="story-viewer-user">
                <img id="svAvatar" src="" alt="">
                <span id="svName"></span>
            </div>
            <div class="story-viewer-actions">
                <button class="story-viewer-del" id="svDelete" onclick="deleteCurrentStory()" data-testid="story-delete" title="Hikayeyi sil" style="display:none">
                    <i class="bi bi-trash"></i>
                </button>
                <button class="story-viewer-close" onclick="closeStoryViewer()" data-testid="story-close"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="story-viewer-media" id="svMedia"></div>
        <div class="story-viewer-caption" id="svCaption"></div>
        <button class="story-nav story-prev" onclick="storyPrev()"><i class="bi bi-chevron-left"></i></button>
        <button class="story-nav story-next" onclick="storyNext()"><i class="bi bi-chevron-right"></i></button>
    </div>
</div>

<form id="storyDeleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script src="{{ asset('assets/js/custom/story-viewer.js') }}"></script>
@endonce

<div class="mood-input-container" style="margin-bottom: 8px;">
    <div class="mood-input-box text-center" style="flex-direction: column;">
        @if (Auth::check() && Auth::user()->avatar)
            <img src="{{ Auth::user()->avatar }}" alt="User Avatar" class="mood-avatar mb-2" style="border: 3px solid #ffffff; border-radius: 50%;">
        @endif
        <div class="mood-text-content">
            <h3 id="greeting-text"></h3>
            <p class="mood-quote">Dibalik setiap kesulitan, tersimpan sebuah kesempatan.</p>
            <small class="mood-author">- Albert Einstein</small>
        </div>
    </div>
</div>
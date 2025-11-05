<style>
    .mood-quote {
        font-style: italic;
    }
    .mood-author {
        font-style: italic;
    }
</style>

<div class="mood-input-container" style="margin-bottom: 8px;">
    <div class="mood-input-box text-center" style="flex-direction: column;">
        <x-user-avatar 
            :avatar-url="Auth::check() ? Auth::user()->avatar : ''" 
            :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''"
            size="70px"
        />
        <div class="mood-text-content">
            <h3 id="greeting-text"></h3>
            <x-mood-quote quote="" author="" />
        </div>
    </div>
</div>
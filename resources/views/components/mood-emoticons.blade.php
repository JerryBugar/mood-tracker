<style>
    .emoticon-background {
        display: inline-block;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        padding: 8px;
        margin: 0;
    }

    .mood-emoticons-container {
        padding: 20px;
    }

    .mood-emoticons-grid > div > div {
        margin: 0 5px;
    }

    @media (min-width: 768px) {
        .mood-emoticons-grid > div > div {
            margin: 0 90px !important;
        }
    }

    @media (max-width: 767px) {
        .mood-emoticons-grid > div > div {
            margin: 0 5px !important;
        }
        
        .emoticon-background {
            padding: 5px;
        }
        
        .mood-emoticons-container {
            padding: 15px 10px;
        }
    }
    
    .emoticon-link { /* Tambahkan style untuk link jika perlu */
        display: inline-block;
        text-decoration: none;
    }
</style>

<div class="mood-emoticons-container" style="background-color: #d98695; border-radius: 15px; margin-top: 0px; text-align: center;">
    <h3 class="d-none d-sm-block mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h3>
    <h5 class="d-block d-sm-none mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h5>
    
    <div class="mood-emoticons-grid mt-3">
        <div class="d-flex justify-content-center align-items-center" style="flex-wrap: nowrap; margin: 0 -15px;">
            <div class="d-md-none text-center">
                <a href="{{ route('mood.modal', ['mood' => 'netral']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" data-mood="netral" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                    </div>
                </a>
            </div>
            <div class="d-none d-md-block text-center">
                <a href="{{ route('mood.modal', ['mood' => 'netral']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" data-mood="netral" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                    </div>
                </a>
            </div>
            
            <div class="d-md-none text-center">
                <a href="{{ route('mood.modal', ['mood' => 'senyum']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" data-mood="senyum" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                    </div>
                </a>
            </div>
            <div class="d-none d-md-block text-center">
                <a href="{{ route('mood.modal', ['mood' => 'senyum']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" data-mood="senyum" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                    </div>
                </a>
            </div>
            
            <div class="d-md-none text-center">
                <a href="{{ route('mood.modal', ['mood' => 'sedih']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" data-mood="sedih" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                    </div>
                </a>
            </div>
            <div class="d-none d-md-block text-center">
                <a href="{{ route('mood.modal', ['mood' => 'sedih']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" data-mood="sedih" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                    </div>
                </a>
            </div>
            
            <div class="d-md-none text-center">
                <a href="{{ route('mood.modal', ['mood' => 'lelah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }}" alt="lelah" class="mood-emoticon emoticon-clickable" data-mood="lelah" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                    </div>
                </a>
            </div>
            <div class="d-none d-md-block text-center">
                <a href="{{ route('mood.modal', ['mood' => 'lelah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }}" alt="lelah" class="mood-emoticon emoticon-clickable" data-mood="lelah" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                    </div>
                </a>
            </div>
            
            <div class="d-md-none text-center">
                <a href="{{ route('mood.modal', ['mood' => 'marah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" data-mood="marah" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                    </div>
                </a>
            </div>
            <div class="d-none d-md-block text-center">
                <a href="{{ route('mood.modal', ['mood' => 'marah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" data-mood="marah" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<style>
    .record-container {
        margin-top: 8px;
        max-height: 400px; /* Atur tinggi maksimum sebelum scroll */
        overflow-y: auto; /* Aktifkan scroll vertikal jika perlu */
    }
    
    .record-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        background-color: #ffffff27;
        border-radius: 12px;
        box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.54); /* Hanya bayangan bawah sesuai border radius */
        border: 2px solid #d98695; /* Warna kontras sesuai tema CereMood */
    }
    
    .record-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 80px;
        margin-right: 15px;
    }
    
    .record-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 5px;
    }
    
    .record-mood {
        font-size: 0.8rem;
        text-align: center;
        font-weight: 500;
        color: #82272c;
    }
    
    .record-center {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        text-align: center;
    }
    
    .record-date {
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
        text-align: center;
    }
    
    .record-reason {
        font-size: 0.9rem;
        color: #555;
        line-height: 1.4;
        text-align: center;
    }
    
    .record-right {
        font-size: 0.9rem;
        font-weight: 500;
        color: #666;
        margin-left: 10px;
        white-space: nowrap;
    }
    
    /* Responsif */
    @media (max-width: 768px) {
        .record-item {
            padding: 12px;
        }
        
        .record-left {
            width: 60px;
        }
        
        .record-avatar {
            width: 40px;
            height: 40px;
        }
        
        .record-mood {
            font-size: 0.7rem;
        }
        
        .record-date {
            font-size: 0.8rem;
        }
        
        .record-reason {
            font-size: 0.8rem;
        }
        
        .record-right {
            font-size: 0.8rem;
        }
    }
    
    /* Untuk semua ukuran layar, pastikan tidak tertutup navbar */
    @media (max-width: 768px) {
        .record-container {
            padding-bottom: 100px; /* Tingkatkan padding untuk mobile */
        }
    }
    
    /* Untuk desktop, tambahkan margin jika diperlukan */
    @media (min-width: 769px) {
        .record-container {
            padding-bottom: 100px;
        }
    }
    
    /* Styling untuk pagination */
    .pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .page-item {
        margin: 0 2px;
    }
    
    .page-link {
        border-radius: 8px;
        background-color: #ffffff;
        border: 1px solid #d98695;
        color: #82272c;
        padding: 8px 12px;
        text-decoration: none;
        display: block;
        transition: all 0.2s ease;
    }
    
    .page-link:hover {
        background-color: #f8f9fa;
        transform: scale(1.05);
    }
    
    @media (max-width: 768px) {
        .page-link {
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .page-item {
            margin: 0 1px;
        }
        
        .pagination {
            flex-wrap: wrap;
        }
    }
    
    @media (min-width: 769px) {
        .page-link {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="record-container">
    <div id="record_container_list">
        @if(Auth::check() && $records->count() > 0)
            @foreach($records as $record)
                {!! view('components._partials.mood_record_item', [
                    'record' => $record,
                    'moodLabels' => $moodLabels,
                    'user' => Auth::user()
                ])->render() !!}
            @endforeach
        @else
            <div id="no-records-message" class="text-center py-5">
                <div style="display: flex; justify-content: center; margin-bottom: 15px;">
                    <svg version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve" fill="#000000" style="width: 48px; height: 48px;">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier"> 
                            <path fill="#d98695" d="M60,52V4c0-2.211-1.789-4-4-4H14v51v3h42v8H10c-2.209,0-4-1.791-4-4s1.791-4,4-4h2v-3V0H8 C5.789,0,4,1.789,4,4v54c0,3.313,2.687,6,6,6h49c0.553,0,1-0.447,1-1s-0.447-1-1-1h-1v-8C59.104,54,60,53.104,60,52z M23,14h12 c0.553,0,1,0.447,1,1s-0.447,1-1,1H23c-0.553,0-1-0.447-1-1S22.447,14,23,14z M42,28H23c-0.553,0-1-0.447-1-1s0.447-1,1-1h19 c0.553,0,1,0.447,1,1S42.553,28,42,28z M49,22H23c-0.553,0-1-0.447-1-1s0.447-1,1-1h26c0.553,0,1,0.447,1,1S49.553,22,49,22z"></path> 
                        </g>
                    </svg>
                </div>
                <p style="color: #82272c; font-size: 1.1rem; font-weight: 500; margin: 0;">Belum ada catatan mood hari ini.</p>
                <p style="color: #6c757d; font-size: 0.9rem; margin-top: 8px;">Ayo ceritakan perasaanmu dengan memilih emoticon di atas!</p>
            </div>
        @endif
    </div>
    
    <div id="pagination-container">
        @include('components._partials.pagination', ['records' => $records])
    </div>
</div>
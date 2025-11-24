<!-- Calendar Navigation -->
<div class="calendar-navigation" style="display: flex; justify-content: center; align-items: center; margin: 10px 0; gap: 10px;">
    <a href="{{ route('calendar.index', ['year' => $previousMonth->year, 'month' => $previousMonth->month]) }}" 
       class="btn btn-outline-secondary" 
       style="color: #82272c; border-color: #82272c; padding: 5px 10px;"
       data-turbo-frame="calendar_frame">
        <i class="bi bi-chevron-left"></i>
    </a>
    
    <h3 style="color: #82272c; margin: 0 10px; flex-shrink: 0;">{{ $monthName }}</h3>
    
    <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
       class="btn btn-outline-secondary" 
       style="color: #82272c; border-color: #82272c; padding: 5px 10px;"
       data-turbo-frame="calendar_frame">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>

<!-- Calendar Grid -->
<div class="calendar-container" style="margin: 0 auto; max-width: 100%; overflow-x: auto;">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            color: #82272c;
            padding: 2px;
            font-size: 0.7rem;
        }
        
        .calendar-day {
            aspect-ratio: 1/1;
            display: flex;
            flex-direction: column;
            border-radius: 6px;
            padding: 2px;
            position: relative;
            min-height: 40px;
            background-color: #ffffff27;
            border: 2px solid #d98695;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.54);
            overflow: hidden;
        }
        
        .calendar-day:hover {
            box-shadow: 0 4px 8px -1px rgba(0, 0, 0, 0.6);
            border-color: #82272c;
            z-index: 1;
        }
        
        .calendar-day-number {
            font-size: 0.7rem;
            font-weight: bold;
            color: #555;
            align-self: flex-start;
        }
        
        .calendar-day.today {
            background-color: #f8f1f1;
            border: 2px solid #82272c;
        }
        
        .calendar-day.today .calendar-day-number {
            color: #82272c;
        }
        
        .calendar-day.other-month {
            background-color: #f8f9fa;
            color: #aaa;
        }
        
        .calendar-day.other-month .calendar-day-number {
            color: #aaa;
        }
        

        
        .mood-emoticon {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
        
        .day-records {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 0.5px !important;
            margin-top: 8px !important;
            height: calc(100% - 20px) !important;
            align-content: center !important;
        }
        
        .mood-emoticon-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .admin-response-indicator-calendar {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #82242d;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            border: 2px solid white;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .admin-response-indicator-calendar:hover {
            transform: scale(1.1);
        }
        
        .admin-response-indicator-calendar i {
            line-height: 1;
        }
        
        /* Responsif untuk mobile */
        @media (max-width: 767px) {
            .calendar-navigation {
                margin: 20px 0 25px 0; /* Lebih banyak margin atas untuk mobile */
                padding: 50px 0; /* Tambahkan padding atas-bawah */
            }
            
            .calendar-navigation h3 {
                font-size: 1.2rem; /* Sedikit perbesar ukuran font untuk mobile */
            }
            
            .calendar-navigation a {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .mood-emoticon {
                width: 20px !important; /* Lebih kecil di mobile */
                height: 20px !important;
            }
            
            .admin-response-indicator-calendar {
                width: 14px !important;
                height: 14px !important;
                font-size: 8px !important;
                top: -3px !important;
                right: -3px !important;
                border-width: 1.5px !important;
            }
        }
        
        .calendar-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            gap: 15px;
        }
        
        .calendar-navigation .btn-outline-secondary {
            color: #82272c;
            border-color: #82272c;
        }
        
        .calendar-navigation h3 {
            color: #82272c;
            margin: 0;
            font-size: 1rem;
        }
        
        .calendar-container {
            margin: 0 auto;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Responsif untuk mobile */
        @media (max-width: 767px) {
            .calendar-navigation {
                margin: 20px 0 25px 0; /* Lebih banyak margin atas untuk mobile */
                padding: 50px 0; /* Tambahkan padding atas-bawah */
            }
            
            .calendar-navigation h3 {
                font-size: 1.2rem; /* Sedikit perbesar ukuran font untuk mobile */
            }
            
            .calendar-navigation a {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .mood-emoticon {
                width: 30px !important; /* Ukuran 30px di mobile */
                height: 30px !important;
            }
            
            .admin-response-indicator-calendar {
                width: 16px !important;
                height: 16px !important;
                font-size: 9px !important;
                top: -3px !important;
                right: -3px !important;
                border-width: 1.5px !important;
            }
            
            .day-records {
                margin-top: -10px !important; /* Atur posisi vertikal untuk mobile */
                height: calc(100% - 15px) !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
            }
        }
        
        /* Responsif untuk desktop */
        @media (min-width: 768px) {
            .calendar-navigation {
                justify-content: space-between;
            }
            
            .calendar-navigation h3 {
                margin: 0 10px; /* Margin kiri-kanan tetap kecil di desktop */
                font-size: 1rem;
            }
            
            .calendar-container {
                max-width: 700px;
                margin: -20px auto 0; /* Menggeser ke atas sejauh 20px */
            }
            
            .calendar-grid {
                max-width: 700px;
                margin: 0 auto;
            }
            
            .mood-emoticon {
                width: 50px;
                height: 50px;
            }
            
            .day-records {
                margin-top: -10px !important; /* Atur posisi vertikal untuk desktop */
                height: calc(100% - 20px) !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
            }
        }
        
        #calendar-day-view {
            margin-top: 10px;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            display: none;
            border: 2px solid #d98695;
            position: relative;
            clear: both;
            z-index: 100;
            width: 100%;
        }
        
        #calendar-day-view.turbo-frame {
            display: block;
        }
        
        .day-view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .day-view-header h4 {
            color: #82272c;
            margin: 0;
            font-size: 1rem;
        }
        
        .record-item {
            display: flex;
            align-items: center;
            padding: 5px;
            margin-bottom: 5px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .record-left {
            width: 40px;
            margin-right: 5px;
            text-align: center;
        }
        
        .record-mood {
            font-size: 0.6rem;
            font-weight: 500;
            color: #82272c;
        }
        
        .record-center {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .record-reason {
            font-size: 0.7rem;
            color: #333;
        }
        
        .record-suggestion {
            font-size: 0.6rem;
            color: #666;
            margin-top: 1px;
        }
        
        .record-suggestion strong {
            color: #82272c;
        }
        
        .record-time {
            font-size: 0.6rem;
            color: #888;
            margin-left: 5px;
        }
        
        .no-records-message {
            text-align: center;
            padding: 10px;
        }
        
        .no-records-message p:first-child {
            color: #82272c;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0;
        }
        
        .no-records-message p:last-child {
            color: #6c757d;
            font-size: 0.65rem;
            margin-top: 2px;
        }
        
        /* Menyembunyikan scrollbar di container kalender */
        .calendar-container::-webkit-scrollbar {
            display: none;
        }
        
        .calendar-container {
            -ms-overflow-style: none;  /* untuk IE dan Edge */
            scrollbar-width: none;  /* untuk Firefox */
        }
    </style>
    
    <div class="calendar-grid">
        <!-- Day headers -->
        <div class="calendar-day-header">Min</div>
        <div class="calendar-day-header">Sen</div>
        <div class="calendar-day-header">Sel</div>
        <div class="calendar-day-header">Rab</div>
        <div class="calendar-day-header">Kam</div>
        <div class="calendar-day-header">Jum</div>
        <div class="calendar-day-header">Sab</div>
        
        <!-- Calendar days -->
        @foreach($calendarDays as $dayData)
            @php
                $isCurrentMonth = $dayData['isCurrentMonth'];
                $isToday = $dayData['isToday'];
                $date = $dayData['date'];
                $records = $dayData['records'];
                $user = Auth::user(); // Ambil user yang sedang login
                $jenisKelamin = $user->jenis_kelamin ?? '';
            @endphp
            <div id="day_{{ $date->format('Y-m-d') }}" class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                <div class="calendar-day-number">{{ $date->day }}</div>

                <div class="day-records">
                    @foreach($records as $record)
                        @php
                            // Tentukan apakah user berjenis kelamin perempuan
                            $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';

                            $emoticonPaths = [
                                'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),

                                'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
                                'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
                                'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
                                'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
                            ];
                            $emoticonPath = $emoticonPaths[$record->mood] ?? $emoticonPaths['netral'];

                            $tooltipText = $record->reason ?? 'Mood: ' . (['netral' => 'Biasa saja', 'senyum' => 'Senang', 'sedih' => 'Sedih', 'lelah' => 'Lelah', 'marah' => 'Marah'][$record->mood] ?? $record->mood);
                            if ($record->admin_response) {
                                $tooltipText .= ' - Direspons oleh Admin/HRD';
                            }
                        @endphp
                        <div class="mood-emoticon-wrapper">
                            <img src="{{ $emoticonPath }}"
                                 alt="{{ $record->mood }}"
                                 class="mood-emoticon {{ $record->mood }}"
                                 data-bs-toggle="tooltip"
                                 title="{{ $tooltipText }}"
                                 onclick="showDayRecords('{{ $date->format('Y-m-d') }}')">
                            @if($record->admin_response)
                                <span class="admin-response-indicator-calendar"
                                      data-bs-toggle="tooltip"
                                      title="Direspons oleh Admin/HRD">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Day View Container as Turbo Frame -->
<turbo-frame id="calendar-day-view" style="margin-top: 20px; padding: 15px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none; width: 100%;">
    <!-- Day records will be loaded here via Turbo -->
</turbo-frame>
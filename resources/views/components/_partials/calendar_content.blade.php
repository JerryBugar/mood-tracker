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
        
        .mood-indicator {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 0.5px;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .mood-indicator.netral { background-color: #A9A9A9; }
        .mood-indicator.senyum { background-color: #FFD700; }
        .mood-indicator.sedih { background-color: #87CEEB; }
        .mood-indicator.lelah { background-color: #D2B48C; }
        .mood-indicator.marah { background-color: #FF6347; }
        
        .day-records {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5px;
            margin-top: auto;
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
        
        /* Responsif untuk desktop */
        @media (min-width: 768px) {
            .calendar-container {
                max-width: 700px;
                margin: -20px auto 0; /* Menggeser ke atas sejauh 20px */
            }
            
            .calendar-grid {
                max-width: 700px;
                margin: 0 auto;
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
        
        /* Menyembunyikan scrollbar di semua browser */
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
            @endphp
            <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                <div class="calendar-day-number">{{ $date->day }}</div>
                
                <div class="day-records">
                    @foreach($records as $record)
                        <span class="mood-indicator {{ $record->mood }}" 
                              data-bs-toggle="tooltip" 
                              title="{{ $record->reason ?? 'Mood: ' . (['netral' => 'Biasa saja', 'senyum' => 'Senang', 'sedih' => 'Sedih', 'lelah' => 'Lelah', 'marah' => 'Marah'][$record->mood] ?? $record->mood) }}"
                              onclick="showDayRecords('{{ $date->format('Y-m-d') }}')">
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Day View Container -->
<div id="calendar-day-view" style="margin-top: 20px; padding: 15px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: none;">
    <!-- Day records will be loaded here via Turbo -->
</div>
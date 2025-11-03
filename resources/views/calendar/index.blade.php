@extends('layouts.internal')

@section('main-content')
    <turbo-frame id="calendar_frame">
        <div class="container-fluid">
            @if (Auth::check())
                <h1 style="color: #82272c; margin-top: 20px; text-align: center;">Kalender Mood</h1>
            @else
                <h1 style="color: #82272c; margin-top: 20px; text-align: center;">Kalender Mood</h1>
            @endif

            @include('components._partials.calendar_content')
    </div>
    </turbo-frame>

    <script>
        function showDayRecords(date) {
            // Dapatkan Turbo Frame
            const calendarDayFrame = document.getElementById('calendar-day-view');
            if (calendarDayFrame) {
                // Atur URL sumber untuk frame
                calendarDayFrame.src = `{{ route('calendar.day', ['date' => ':date']) }}`.replace(':date', date);
                
                // Tampilkan frame
                calendarDayFrame.style.display = 'block';
            }
        }
        
        // Initialize tooltips when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Reinitialize tooltips when Turbo loads
        document.addEventListener('turbo:load', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

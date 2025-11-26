@extends('layouts.internal')

@push('head')
    <meta name="turbo-cache-control" content="no-cache">
@endpush

@section('main-content')
    <turbo-frame id="calendar_frame" data-turbo-cache="false" data-turbo-permanent>
        <div class="container-fluid">
            @if (Auth::check())
                <h1 style="color: #82272c; margin-top: 20px; text-align: center;">Calendar Mood</h1>
            @else
                <h1 style="color: #82272c; margin-top: 20px; text-align: center;">Calendar Mood</h1>
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

        // Reinitialize tooltips when Turbo renders new content
        document.addEventListener('turbo:before-stream-render', function(event) {
            // This event is triggered when a Turbo Stream response is about to be rendered
        });

        // Reinitialize tooltips after Turbo Stream renders - optimized for mobile
        document.addEventListener('turbo:render', function() {
            // Use requestAnimationFrame instead of setTimeout for better performance on mobile
            requestAnimationFrame(function() {
                const newTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                newTooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    // Check if tooltip instance already exists by verifying Bootstrap data
                    const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (!tooltipInstance) {
                        // Only initialize tooltip if it doesn't already exist
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                });
            });
        });
    </script>
@endsection

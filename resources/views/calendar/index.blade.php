@extends('layouts.internal')

@section('main-content')
    <turbo-frame id="calendar_frame">
        <div class="container-fluid">
            @if (Auth::check())
                <h1 style="color: #82272c; margin-top: 20px;">Kalender Mood</h1>
            @else
                <h1 style="color: #82272c; margin-top: 20px;">Kalender Mood</h1>
            @endif

            @include('components._partials.calendar_content')
    </div>
    </turbo-frame>

    <script>
        function showDayRecords(date) {
            // Scroll to the day view
            const dayView = document.getElementById('calendar-day-view');
            dayView.scrollIntoView({ behavior: 'smooth' });
            
            // Make an AJAX request to load the day's records
            const url = `{{ route('calendar.day', ['date' => ':date']) }}`.replace(':date', date);
            
            fetch(url, {
                headers: {
                    'Accept': 'text/vnd.turbo-stream.html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(data => {
                // Parse the turbo stream response
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const template = doc.querySelector('template');
                
                if (template) {
                    // Update the day view with the content from the turbo stream
                    dayView.innerHTML = template.innerHTML;
                    dayView.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading day records:', error);
            });
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

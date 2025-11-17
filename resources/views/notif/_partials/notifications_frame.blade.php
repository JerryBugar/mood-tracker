<turbo-frame id="notifications_frame" data-turbo-action="replace" data-turbo-permanent>
    @include('notif._partials.notifications_list', ['notifications' => $notifications])
</turbo-frame>


<nav id="main-bottom-nav" class="bottom-nav" style="background-color: #82272c;" data-turbo-permanent>
    <div class="nav-active-background"></div>
    <a href="{{ url('/home') }}" class="nav-item {{ Request::is('home') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-house-door-fill"></i>
        <span class="nav-text">Home</span>
    </a>
    <a href="{{ url('/calendar') }}" class="nav-item {{ Request::is('calendar') ? 'active' : '' }}" data-turbo-action="advance">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
        <span class="nav-text">Calendar</span>
    </a>
    <a href="{{ url('/notif') }}" class="nav-item {{ Request::is('notif') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-bell-fill"></i>
        <span class="nav-text">Notif</span>
    </a>
    <a href="{{ url('/profile') }}" class="nav-item {{ Request::is('profile') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-person-fill"></i>
        <span class="nav-text">Profile</span>
    </a>
</nav>

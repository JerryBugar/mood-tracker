<nav id="main-bottom-nav" class="bottom-nav" style="background-color: #82272c;" data-turbo-permanent>
    <div class="nav-active-background"></div>
    <a href="{{ url('/home') }}" class="nav-item {{ Request::is('home') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i>
        <span class="nav-text">Home</span>
    </a>
    <a href="{{ url('/record') }}" class="nav-item {{ Request::is('record') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
        <span class="nav-text">Record</span>
    </a>
    <a href="{{ url('/notif') }}" class="nav-item {{ Request::is('notif') ? 'active' : '' }}">
        <i class="bi bi-bell-fill"></i>
        <span class="nav-text">Notif</span>
    </a>
    <a href="{{ url('/profile') }}" class="nav-item {{ Request::is('profile') ? 'active' : '' }}">
        <i class="bi bi-person-fill"></i>
        <span class="nav-text">Profile</span>
    </a>
</nav>

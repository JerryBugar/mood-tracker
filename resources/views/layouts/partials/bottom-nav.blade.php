<nav class="bottom-nav">
    <a href="{{ url('/home') }}" class="nav-item {{ Request::is('home') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i>
    </a>
    <a href="{{ url('/record') }}" class="nav-item {{ Request::is('record') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
    </a>
    <a href="{{ url('/notif') }}" class="nav-item {{ Request::is('notif') ? 'active' : '' }}">
        <i class="bi bi-bell-fill"></i>
    </a>
    <a href="{{ url('/profile') }}" class="nav-item {{ Request::is('profile') ? 'active' : '' }}">
        <i class="bi bi-person-fill"></i>
    </a>
</nav>

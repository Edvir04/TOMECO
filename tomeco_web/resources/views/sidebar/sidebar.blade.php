{{-- SIDEBAR --}}
<aside class="tomeco-sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('assets/Logo.png') }}" alt="TOMECO Logo">
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('admin.ticket-issuance') }}" class="{{ request()->routeIs('admin.ticket-issuance') ? 'active' : '' }}">Ticket Issuance</a></li>
            @if(auth('superadmin')->check())
                <li><a href="{{ route('admin.violations') }}" class="{{ request()->routeIs('admin.violations') ? 'active' : '' }}">Violations</a></li>
                <li><a href="{{ route('admin.penalty') }}" class="{{ request()->routeIs('admin.penalty') ? 'active' : '' }}">Penalty Recommendation</a></li>
            @endif
            <li><a href="{{ route('admin.accounts') }}" class="{{ request()->routeIs('admin.accounts') ? 'active' : '' }}">Accounts</a></li>
            <li><a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">Profile</a></li>
        </ul>
    </nav>

    <div class="sidebar-bottom">
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</aside>

{{-- Sidebar-only CSS (kept in this Blade) --}}
<style>
/* Match these to your actual navbar height and sidebar width */
:root{
    /* --nav-h: 58px;     height of your top navbar */
    --sb-w: 220px;     /* width of sidebar */
}

/* Sidebar sits BELOW the navbar and fills remaining height */
.tomeco-sidebar{
    position: fixed;
    top: var(--nav-h);
    top: 0;
    left: 0;
    width: var(--sb-w);
    height: 100vh;
    background: linear-gradient(to bottom, #8B0000, #C00000);
    border-radius: 0 0px 24px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 18px 0 14px;
    overflow-y: auto;                     /* prevents vertical overlap */
    z-index: 100;                         /* stay below navbar if navbar has higher z-index */
}

.sidebar-logo img{ width: 90px; margin: 6px 0 16px; }

.sidebar-nav{ width: 100%; flex: 1; }
.sidebar-nav ul{ list-style: none; padding: 0 14px; margin: 0; }
.sidebar-nav li{ margin: 12px 0; text-align: center; }
.sidebar-nav a{
    display: block;
    padding: 10px 16px;
    border-radius: 18px;
    text-decoration: none;
    color: #fff;
    font-size: 14px;
    transition: .2s ease;
}
.sidebar-nav a.active,
.sidebar-nav a:hover{
    background: #ffffff1a;
    box-shadow: 0 6px 14px rgba(0,0,0,.18);
}

.sidebar-bottom{ width: 100%; padding: 10px 14px; text-align: center; }
.logout-btn{
    width: 100%;
    background: transparent;
    border: 1px solid #ffffff55;
    color: #fff;
    padding: 10px 14px;
    border-radius: 14px;
    cursor: pointer;
    transition: .2s ease;
}
.logout-btn:hover{ background:#ffffff1a; }


/* Safety: if screen height is small, keep the rounded corner visible but allow scroll */
@media (max-height: 640px){
    .tomeco-sidebar{ border-radius: 0 18px 0 0; }
}
</style>

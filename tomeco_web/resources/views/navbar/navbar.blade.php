<header class="navbar {{ !empty($withSidebar) ? 'navbar--with-sidebar' : '' }}">
  <div class="navbar-container">
    @if (request()->routeIs('admin.login') || request()->routeIs('login') || request()->routeIs('welcome'))
      <a href="{{ route('welcome') }}" class="nav-left">
        <img src="{{ asset('assets/Logo.png') }}" alt="TOMECO Logo" class="navbar-logo">
        <span class="navbar-title">TOMECO</span>
      </a>
    @else
      @php
        $route = \Illuminate\Support\Facades\Route::currentRouteName();
        $map = [
          'layout.dashboard'            => 'Dashboard',
          'layout.dashboard.superadmin' => 'Dashboard',
          'accounts.index'              => 'Accounts',
          'accounts.create'             => 'Accounts',
          'accounts.store'              => 'Accounts',
          'tickets.index'               => 'Ticket Issuance',
          'tickets.create'              => 'Ticket Issuance',
        ];
        $fallbackFromSegment = request()->segment(1)
          ? \Illuminate\Support\Str::title(str_replace('-', ' ', request()->segment(1)))
          : 'Dashboard';
        $computedTitle = $map[$route] ?? $fallbackFromSegment;
      @endphp
      <div class="nav-left">
        <span class="navbar-page">{{ trim($__env->yieldContent('navTitle')) ?: $computedTitle }}</span>
      </div>
    @endif
  </div>
</header>


<style>
:root {
  --nav-h: 58px;    /* navbar height */
  --sb-w: 220px;    /* sidebar width */
}

.navbar {
  background:linear-gradient(to bottom, #8B0000, #C00000);
  height: var(--nav-h);
  position: fixed;
  top: 0;
  left: 0;          /* default: full width */
  width: 100%;
  z-index: 200;
  display: flex;
  align-items: center;
  padding: 0 16px;
}

.navbar--with-sidebar {
  left: var(--sb-w);                 /* shift if logged in */
  width: calc(100% - var(--sb-w));   /* fill remaining space */
}

.navbar-container {
  display:flex; align-items:center; justify-content:space-between;
  padding:0 16px; width:100%;
}
.nav-left { display:flex; align-items:center; gap:12px; text-decoration:none; }
.navbar-logo{ height:40px; width:auto; object-fit:contain; }
.navbar-title{ font-size:1.25rem; font-weight:800; color:#fff; }
.navbar-page{ font-size:1.125rem; font-weight:700; color:#fff; }

</style>

@php
  $isAuthRoute = request()->routeIs('login') || request()->routeIs('admin.login');
  // Only show sidebar if authenticated AND not on login page
  $withSidebar = !$isAuthRoute && (auth('admin')->check() || auth('superadmin')->check());
  $isWelcome   = request()->routeIs('welcome');
  $bodyClass = $__env->yieldContent('body-class');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">


<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TOMECO')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
<style>
  :root { --nav-h: 58px; --sb-w: 220px; }

  /* NAVBAR: full width by default; shifts only if logged in */
  .navbar{
    background:#962e2e; height:var(--nav-h);
    position:fixed; top:0; left:0; width:100%;
    z-index:200; display:flex; align-items:center; padding:0 16px;
  }
  .navbar--with-sidebar{
    left:var(--sb-w);
    width:calc(100% - var(--sb-w));
  }

  /* Sidebar (only when logged in) */
  .tomeco-sidebar{
    position:fixed; top:0; left:0; width:var(--sb-w); height:100vh;
    background:linear-gradient(to bottom,#8B0000,#C00000);
    display:flex; flex-direction:column; align-items:center;
    padding:18px 0 14px; overflow-y:auto; z-index:100;
    border-radius:0 0 24px 0;
  }

  /* Base content: below navbar; only push right when logged in */
  .app-content{
    margin-top:var(--nav-h);
    min-height:calc(100vh - var(--nav-h));
    padding:16px;
    background:#f6f6f6;
  }
  .with-sidebar .app-content{ margin-left:var(--sb-w); }

  /* ---------- Auth (login) pages: perfectly centered, no scroll ---------- */
  .is-auth{
    height:100vh;
    overflow:hidden;         /* disable page scroll */
  }
  .is-auth .app-content{
    margin:0;                /* ignore navbar/sidebar margins */
    min-height:100vh;
    padding:0;
    display:flex;
    align-items:center;
    justify-content:center;  /* center the login container */
    background:#f6f6f6;
  }

  /* ---------- Welcome page: full viewport minus navbar ---------- */
  .is-welcome{
    min-height:100vh;
    overflow-x:hidden;
  }
  .is-welcome .app-content{
    margin:0;
    min-height:calc(100vh - var(--nav-h));
    padding:0;
    padding-top:var(--nav-h); /* space under fixed navbar */
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f6f6f6;
  }
</style>

</head>
<body class="{{ $withSidebar ? 'with-sidebar' : '' }} {{ $isAuthRoute ? 'is-auth' : '' }} {{ $isWelcome ? 'is-welcome' : '' }} {{ $bodyClass ? $bodyClass : '' }}">
  {{-- Navbar --}}
  @include('navbar.navbar', ['withSidebar' => $withSidebar])

  <div class="app-shell">
    @if($withSidebar)
      @include('sidebar.sidebar')
    @endif

    <main class="app-content">
      @yield('content')
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
  
  @if($withSidebar)
  <script>
    // Handle session timeout and auto-logout on tab close
    let lastActivity = Date.now();
    const SESSION_LIFETIME_MINUTES = {{ config('session.lifetime', 120) }};
    const SESSION_TIMEOUT_MS = SESSION_LIFETIME_MINUTES * 60 * 1000; // Convert minutes to milliseconds
    
    // Track user activity to reset timeout
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
      document.addEventListener(event, () => {
        lastActivity = Date.now();
      }, true);
    });
    
    // Check session validity periodically (every 30 seconds)
    setInterval(function() {
      const timeSinceActivity = Date.now() - lastActivity;
      // Check if session should have expired (with 1 minute buffer)
      if (timeSinceActivity > (SESSION_TIMEOUT_MS - 60000)) {
        // Session likely expired - redirect to login
        // Laravel will handle actual session validation on next request
        window.location.href = '{{ route("admin.login") }}';
      }
    }, 30000); // Check every 30 seconds
    
    // Attempt to logout on tab close (not always reliable, but worth trying)
    window.addEventListener('beforeunload', function(e) {
      if (navigator.sendBeacon) {
        try {
          // Send a logout request via beacon (doesn't block page unload)
          const url = '{{ route("admin.logout.auto") }}';
          navigator.sendBeacon(url);
        } catch(err) {
          // Ignore errors - not critical if this fails
        }
      }
    });
    
    // Handle page visibility change (tab switch, minimize)
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') {
        // Tab is visible again - update last activity time
        lastActivity = Date.now();
      }
    });
  </script>
  @endif
</body>
</html>

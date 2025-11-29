{{-- resources/views/welcome.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>TOMECO Portal</title>
  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    :root{
      --nav-h: 58px;           /* keep in sync with your navbar */
      --tomeco-red:#962e2e;
    }

    html, body { height:100%; }
    body{
      margin:0;
      font-family: 'Nunito', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      /* soft overlay + image bg */
      background:
        linear-gradient(rgba(255,255,255,.75), rgba(255,255,255,.75)),
        url('{{ asset('assets/bg.jpg') }}') no-repeat center/cover;
      display:flex;
      flex-direction:column;
    }

    /* Navbar-aware hero wrapper */
    .main-hero{
      /* reserve space below fixed navbar */
      padding-top: var(--nav-h);

      /* fill the visible viewport minus the navbar */
      min-height: calc(100vh  - var(--nav-h));
      min-height: calc(100svh - var(--nav-h)); /* mobile-safe */

      /* center the inner hero */
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .container { width:100%; max-width:1100px; margin:0 auto; padding:0 16px; }

    .hero{
      width:100%;
      display:flex;
      flex-direction:column;
      align-items:center;
      text-align:center;
      padding:32px 16px;
    }

    .hero h2{
      margin:0 0 16px;
      font-size:1.15rem; font-weight:700; letter-spacing:.5px; color:#111827;
    }

    .hero img{
      width:170px; height:170px; object-fit:contain; margin:10px 0 14px;
      filter: drop-shadow(0 4px 10px rgba(0,0,0,.15));
    }

    .hero h1{
      margin:10px 0 0;
      font-size:1.6rem; font-weight:900; color:#111827;
    }

    .login-btn{
      display:inline-block;
      background:#dc2626; color:#fff; font-weight:700; text-decoration:none;
      padding:12px 28px; border-radius:10px; margin:26px 0 40px;
      transition:opacity .2s ease, transform .08s ease;
    }
    .login-btn:hover{ opacity:.95; }
    .login-btn:active{ transform:translateY(1px); }

    @media (min-width:640px){
      .hero h2{ font-size:1.25rem; }
      .hero h1{ font-size:1.9rem; }
      .hero img{ width:190px; height:190px; }
    }
  </style>
</head>
<body>

  {{-- Use your existing fixed navbar --}}
  @include('navbar.navbar')

  {{-- NEW: wrap content in a navbar-aware main --}}
  <main class="main-hero">
    <div class="container">
      <div class="hero">
        {{-- Full meaning on top --}}
        <h2>Traffic Operations Management, Enforcement &amp; Control Office</h2>

        {{-- Centered logo --}}
        <img src="{{ asset('assets/Logo.png') }}" alt="TOMECO Logo">

        {{-- Bold portal text at the bottom --}}
        <h1>TOMECO {{ strtoupper(env('APP_PORTAL_TYPE', 'admin')) === 'VIOLATOR' ? 'VIOLATOR' : 'ADMIN' }} PORTAL</h1>

        {{-- Show appropriate link based on portal type --}}
        @if(strtoupper(env('APP_PORTAL_TYPE', 'admin')) === 'VIOLATOR')
          {{-- Violator Server Instance - Show violator portal link --}}
          <a class="login-btn" href="{{ route('violator.portal') }}" style="background: #059669;">Enter Violator Portal</a>
        @else
          {{-- Admin Server Instance - Show admin login link --}}
          <a class="login-btn" href="{{ route('admin.login') }}" style="background: #dc2626;">Admin Login</a>
        @endif
      </div>
    </div>
  </main>

</body>
</html>

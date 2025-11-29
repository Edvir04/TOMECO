@extends('layout.app')

@section('title','Login — TOMECO')
@section('body-class','login-page')

@section('content')
<div class="login-shell">
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <img src="{{ asset('assets/Logo.png') }}" alt="Logo" class="logo">
        <h2 class="login-title">TOMECO</h2>
        <p class="login-subtitle">Traffic Operations Management, Enforcement & Control Office</p>
      </div>

      <div class="login-body">
        <form method="POST" action="{{ route('admin.login.post') }}">
          @csrf

          <div class="form-group">
            <input id="username" type="text"
              class="form-control @error('username') is-invalid @enderror"
              name="username" value="{{ old('username') }}" required autocomplete="username" autofocus
              placeholder="Username">
            @error('username') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
          </div>

          <div class="form-group">
            <input id="password" type="password"
              class="form-control @error('password') is-invalid @enderror"
              name="password" required autocomplete="current-password"
              placeholder="Password">
            @error('password') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
          </div>

          <button type="submit" class="btn-login">Sign In</button>

      </div>
    </div>
  </div>
</div>
@endsection

<style>
  /* Fallback (in case layout doesn't define it) */
  :root { --nav-h: 58px; }

  /* Remove all scrollbars from html and body on login page */
  html.login-page,
  html.is-auth,
  body.login-page,
  body.is-auth {
    overflow: hidden !important;
    height: 100vh !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Keep the page from scrolling */
  body.login-page,
  body.is-auth {
    background:#f6f7fb !important;
    position: fixed !important;
  }

  /* Neutralize layout offsets on login page */
  .login-page .app-content,
  .is-auth .app-content {
    margin:0 !important;               /* remove layout margins */
    padding:0 !important;
    height:100vh !important;
    min-height:100vh !important;
    max-height:100vh !important;
    overflow:hidden !important;
    background:#f6f6f6;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* The area that sits below the fixed navbar and centers the card */
  .login-shell{
    width: 100%;
    height: 100%;
    display:flex; 
    align-items:center; 
    justify-content:center;
    padding:16px;                            /* breathing room on small screens */
    overflow:hidden;                         /* no scrollbar */
    box-sizing: border-box;
  }

  .login-container{width:100%; max-width:420px}
  .login-card{background:#fff; border-radius:12px; padding:32px; box-shadow:0 8px 24px rgba(0,0,0,.08)}
  .login-header{display:flex; flex-direction:column; text-align:center; align-items:center; margin-bottom:24px}
  .login-header .logo{width:180px; height:auto; margin:0 auto 12px}
  .login-title{margin:0; font-size:1.5rem; font-weight:800; color:#962e2e}
  .login-subtitle{font-size:.9rem; color:#555}
  .form-group{margin-bottom:16px}
  .form-control{width:100%; padding:10px; border:1px solid #ddd; border-radius:6px}
  .invalid-feedback{display:block; color:#b3261e; font-size:.85rem; margin-top:6px}
  .btn-login{width:100%; padding:12px; background:#962e2e; color:#fff; font-weight:600; border:none; border-radius:6px; cursor:pointer}
  .btn-login:hover{background:#7a2323}
</style>

<script>
  // Ensure no scrollbars on login page
  document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.style.overflow = 'hidden';
    document.documentElement.style.height = '100%';
    document.body.style.overflow = 'hidden';
    document.body.style.height = '100%';
    document.body.style.margin = '0';
    document.body.style.padding = '0';
  });
</script>

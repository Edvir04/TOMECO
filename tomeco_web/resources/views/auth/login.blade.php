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
            <div class="password-input-wrapper">
              <input id="password" type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password" required autocomplete="current-password"
                placeholder="Password">
              <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Toggle password visibility">
                <svg class="eye-icon eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-icon eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
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
  .form-control{width:100%; padding:10px; padding-right:40px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box}
  .invalid-feedback{display:block; color:#b3261e; font-size:.85rem; margin-top:6px}
  .btn-login{width:100%; padding:12px; background:#962e2e; color:#fff; font-weight:600; border:none; border-radius:6px; cursor:pointer}
  .btn-login:hover{background:#7a2323}
  
  /* Password input wrapper with toggle button */
  .password-input-wrapper{
    position:relative;
    width:100%;
  }
  .password-toggle-btn{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    cursor:pointer;
    padding:5px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#666;
    transition:color 0.2s;
  }
  .password-toggle-btn:hover{
    color:#962e2e;
  }
  .password-toggle-btn:focus{
    outline:none;
    color:#962e2e;
  }
  .eye-icon{
    display:block;
  }
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
    
    // Password visibility toggle
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const eyeOpen = passwordToggle.querySelector('.eye-open');
    const eyeClosed = passwordToggle.querySelector('.eye-closed');
    
    if (passwordToggle && passwordInput) {
      passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeOpen.style.display = isPassword ? 'none' : 'block';
        eyeClosed.style.display = isPassword ? 'block' : 'none';
      });
    }
    
    // Prevent back button from showing cached login page
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function() {
      window.history.pushState(null, null, window.location.href);
    };
  });
  
  // Prevent back button from accessing login page after logout
  // This is handled server-side with cache headers, but adding client-side protection too
  if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
    window.location.reload();
  }
</script>

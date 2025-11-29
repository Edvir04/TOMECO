@extends('layouts.app')

@section('body-class', 'login-page')   <!-- 👈 This makes <body class="login-page"> -->
@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('assets/newlogo.png') }}" alt="Logo" class="logo">
            <h2 class="login-title">TOMECO</h2>
            <p class="login-subtitle">Traffic Operations Management, Enforcement & Control Office</p>
        </div>

        <div class="login-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <input id="username" type="username"
                        class="form-control @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required autocomplete="username" autofocus
                        placeholder="username or TOMECO Client Number">

                    @error('username')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" required autocomplete="current-password"
                        placeholder="Password">

                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-options">
                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">
                            Forgot Password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>
    </div>
</div>
@endsection

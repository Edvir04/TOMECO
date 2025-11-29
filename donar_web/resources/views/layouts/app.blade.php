<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TOMECO</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class')">
    <div id="app">
        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-tomeco shadow-sm fixed-top">
            <div class="container-fluid px-3 d-flex align-items-center justify-content-between">
                <!-- Left: Logo + text -->
                <div class="d-flex align-items-center">
                    <a href="{{ url('home') }}" class="brand-link">
                        <img src="{{ asset('assets/newlogo.png') }}" alt="TOMECO Logo" class="logo-img">
                        <span class="brand-text">TOMECO</span>
                    </a>
                </div>

                <!-- Right: Logout button -->
                @auth
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light fw-bold">
                        Logout
                    </button>
                </form>
                @endauth
            </div>
        </nav>

        <main class="py-4 mt-5">
            @yield('content')
        </main>
    </div>

    <!-- Custom CSS -->
    <style>
        .bg-tomeco {
            background-color: #962e2e; /* brand color */
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none !important;
            color: white;
        }

        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            line-height: 1;
            display: flex;
            align-items: center;
        }

        .btn-light {
            color: #962e2e;
        }
    </style>
</body>
</html>

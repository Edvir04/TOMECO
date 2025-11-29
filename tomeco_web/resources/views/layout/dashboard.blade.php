@extends('layout.app')

@section('content')
@php
    // We only push the content when logged-in (sidebar visible)
    $hasSidebar = session()->has('user');
@endphp

<div class="page {{ $hasSidebar ? 'with-sb' : '' }}">
    <header class="page-head">
        <h1>Welcome to TOMECO Dashboard</h1>
        <p class="sub">This is your main control panel. Use the sidebar to navigate through the system.</p>
    </header>

    <section class="metrics">
        <article class="metric-card">
            <h2>Total Tickets</h2>
            <div class="metric-value">123</div>
        </article>

        <article class="metric-card">
            <h2>Active Users</h2>
            <div class="metric-value">45</div>
        </article>

        <article class="metric-card">
            <h2>Pending Issues</h2>
            <div class="metric-value">8</div>
        </article>
    </section>
</div>

<style>
/* ---- Layout offsets (match your sidebar width) ---- */
:root{
    --sb-w: 220px;        /* sidebar width */
    --gap: 24px;          /* spacing between sidebar and content */
}

.page{
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 20px;
}

/* push content only when sidebar is present */
.page.with-sb{
    margin-left: calc(var(--sb-w) + var(--gap));
}

/* Optional: keep things usable on small screens */
@media (max-width: 900px){
    .page.with-sb{
        margin-left: calc(var(--sb-w) + 12px);
    }
}
@media (max-width: 680px){
    .page.with-sb{
        margin-left: calc(var(--sb-w) - 40px); /* tighter on narrow view */
    }
}

/* ---- Page styles ---- */
.page-head h1{
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 8px;
}
.page-head .sub{
    margin: 0 0 18px;
    color: #444;
}

/* metric cards */
.metrics{
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 20px;
}
.metric-card{
    grid-column: span 4;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 18px rgba(0,0,0,.08);
    padding: 22px;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: transform .12s ease, box-shadow .12s ease;
}
.metric-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 12px 22px rgba(0,0,0,.12);
}
.metric-card h2{
    font-size: 18px;
    margin: 0 0 8px;
    color: #8B0000; /* TOMECO red */
    font-weight: 700;
}
.metric-value{
    font-size: 28px;
    font-weight: 800;
}

/* responsive columns */
@media (max-width: 1024px){
    .metric-card{ grid-column: span 6; }  /* 2 per row */
}
@media (max-width: 640px){
    .metric-card{ grid-column: span 12; } /* 1 per row */
}
</style>
@endsection

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
        <article class="metric-card clickable" data-metric="tickets" onclick="openModal('tickets')">
            <h2>Total Tickets</h2>
            <div class="metric-value">{{ $totalTickets ?? 0 }}</div>
        </article>

        <article class="metric-card clickable" data-metric="users" onclick="openModal('users')">
            <h2>Total Users</h2>
            <div class="metric-value">{{ $totalUsers ?? 0 }}</div>
        </article>

        <article class="metric-card clickable" data-metric="pending" onclick="openModal('pending')">
            <h2>Pending Issues</h2>
            <div class="metric-value">{{ $pendingTickets ?? 0 }}</div>
        </article>

        <article class="metric-card clickable" data-metric="period" onclick="openModal('period')">
            <h2>Ticket Report</h2>
            <div class="metric-subtitle">Today: {{ $todayTickets ?? 0 }} | Week: {{ $weekTickets ?? 0 }} | Month: {{ $monthTickets ?? 0 }}</div>
        </article>

    </section>

    {{-- Violations Chart Section --}}
    <section class="violations-chart-section">
        <div id="violationsChartContainer">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading violations data...</p>
            </div>
        </div>
    </section>
</div>

{{-- Modal for displaying detailed lists --}}
<div class="modal" id="detailsModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card">
        <div class="modal-head">
            <div id="modalTitle">Details</div>
            <button class="btn btn-light" id="closeModal" aria-label="Close dialog" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading...</p>
            </div>
        </div>
    </div>
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
    grid-column: span 3;
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
.metric-card.clickable{
    cursor: pointer;
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
.metric-subtitle{
    font-size: 13px;
    color: #6b7280;
    margin-top: 4px;
    font-weight: 500;
}

/* responsive columns */
@media (max-width: 1400px){
    .metric-card{ grid-column: span 4; }  /* 3 per row */
}
@media (max-width: 1200px){
    .metric-card{ grid-column: span 6; }  /* 2 per row */
}
@media (max-width: 768px){
    .metric-card{ grid-column: span 6; }  /* 2 per row */
}
@media (max-width: 640px){
    .metric-card{ grid-column: span 12; } /* 1 per row */
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 30px;
    overflow-y: auto;
}

.modal.open {
    display: flex;
}

.modal-card {
    background: #fff;
    width: min(900px, 95%);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    animation: slideDown 0.25s ease forwards;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1 1 auto;
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid #eee;
    background: #fafafa;
    color: #111;
    font-weight: 600;
    font-size: 18px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    border-radius: 8px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 14px;
}

.btn-light {
    background: #f3f4f6;
    color: #111;
}

.btn-light:hover {
    background: #e5e7eb;
}

/* Table styles for modal */
.details-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.details-table thead th {
    background: #fafafa;
    border-bottom: 2px solid #e5e7eb;
    text-align: left;
    padding: 12px;
    font-weight: 600;
    color: #111;
}

.details-table tbody td {
    border-top: 1px solid #f0f0f0;
    padding: 12px;
    vertical-align: middle;
}

.details-table tbody tr:hover {
    background: #f9fafb;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-paid {
    background-color: #10b981;
    color: white;
}

.status-unpaid {
    background-color: #ef4444;
    color: white;
}

.role-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.role-admin {
    background: #2563eb;
    color: white;
}

.role-superadmin {
    background: #111;
    color: white;
}

.role-enforcer {
    background: #16a34a;
    color: white;
}

.table-empty {
    padding: 40px;
    text-align: center;
    color: #6b7280;
}

/* Spinner */
.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

/* Tabs for period report */
.tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.tab-button {
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
}

.tab-button:hover {
    color: #111;
    background: #f9fafb;
}

.tab-button.active {
    color: #8B0000;
    border-bottom-color: #8B0000;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content-header {
    margin-bottom: 16px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
    font-weight: 600;
    color: #111;
}

@media (max-width: 640px) {
    .modal {
        align-items: flex-start;
        padding-top: 60px;
    }
    
    .details-table {
        font-size: 12px;
    }
    
    .details-table thead th,
    .details-table tbody td {
        padding: 8px;
    }
    
    .tabs {
        flex-wrap: wrap;
    }
    
    .tab-button {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .chart-bars-vertical {
        gap: 12px;
        padding: 10px;
        justify-content: flex-start;
    }
    
    .chart-bar-container-vertical {
        min-width: 100px;
        flex-shrink: 0;
    }
    
    .chart-bar-wrapper-vertical {
        height: 250px;
    }
    
    .chart-bar-label-vertical {
        font-size: 11px;
        min-height: 50px;
    }
    
    .chart-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .chart-date-range {
        margin-left: 0;
        margin-top: 8px;
        flex-wrap: wrap;
    }
}

/* Chart/Graph styles for violations */
.chart-container {
    padding: 20px;
}

.chart-header-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.chart-header-btn {
    padding: 12px 24px;
    border: 2px solid #d1d5db;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    color: #4b5563;
    transition: all 0.2s;
}

.chart-header-btn:hover {
    background: #f9fafb;
    border-color: #8B0000;
}

.chart-header-btn.active {
    background: #8B0000;
    color: white;
    border-color: #8B0000;
}

.chart-filters {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.chart-filter-btn {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    transition: all 0.2s;
}

.chart-filter-btn:hover {
    background: #f9fafb;
    border-color: #8B0000;
}

.chart-filter-btn.active {
    background: #8B0000;
    color: white;
    border-color: #8B0000;
}

.chart-date-range {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-left: auto;
}

.chart-date-input {
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
}

.chart-show-all {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 0px;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 6px;
}

.chart-show-all label {
    font-size: 14px;
    font-weight: 500;
    color: #111;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-show-all input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.chart-bars-wrapper {
    max-height: 500px;
    overflow-y: auto;
}

.chart-bar-container {
    margin-bottom: 20px;
}

.chart-bar-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
}

.chart-bar-label-text {
    flex: 1;
    min-width: 0;
    font-weight: 500;
    color: #111;
}

.chart-bar-label-percentage {
    color: #6b7280;
    font-weight: 600;
    margin-left: 12px;
    white-space: nowrap;
    font-size: 14px;
}

.chart-bar-label-count {
    color: #8B0000;
    font-weight: 600;
    white-space: nowrap;
    margin-left: 8px;
    font-size: 14px;
}

/* Violations Chart Section */
.violations-chart-section {
    margin-top: 40px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 18px rgba(0,0,0,.08);
    padding: 30px;
}

.violations-chart-section h2 {
    font-size: 24px;
    font-weight: 700;
    color: #8B0000;
    margin-bottom: 24px;
}

.chart-bar-wrapper {
    width: 100%;
    height: 300px;
    background: #f3f4f6;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.chart-bar {
    width: 100%;
    background: linear-gradient(180deg, #9333ea, #a855f7);
    border-radius: 8px 8px 0 0;
    transition: height 0.6s ease;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 12px 8px 8px 8px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    min-height: 40px;
}

.chart-bar.highlighted {
    background: linear-gradient(180deg, #6b21a8, #7e22ce);
    box-shadow: 0 4px 8px rgba(107, 33, 168, 0.3);
    position: relative;
}

.chart-bar.highlighted::after {
    content: '';
    position: absolute;
    top: -4px;
    left: 0;
    right: 0;
    height: 3px;
    background: #6b21a8;
    border-radius: 8px 8px 0 0;
}

/* Vertical bar chart layout */
.chart-bars-wrapper-outer {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 10px 0;
}

.chart-bars-wrapper-outer::-webkit-scrollbar {
    height: 8px;
}

.chart-bars-wrapper-outer::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 4px;
}

.chart-bars-wrapper-outer::-webkit-scrollbar-thumb {
    background: #8B0000;
    border-radius: 4px;
}

.chart-bars-wrapper-outer::-webkit-scrollbar-thumb:hover {
    background: #660000;
}

/* Line Chart Styles */
.chart-line-wrapper-outer {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    padding: 20px 0;
}

.chart-line-container {
    position: relative;
    min-width: fit-content;
    padding-bottom: 100px;
}

.chart-line-container.centered {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.chart-line-container.show-all {
    display: block;
}

.chart-line-svg {
    display: block;
    width: 100%;
    height: 300px;
    background: #f9fafb;
    border-radius: 8px;
    overflow: visible;
}

.chart-line-svg-centered {
    width: auto;
}

.chart-line-path {
    stroke-linecap: round;
    stroke-linejoin: round;
}

.chart-line-point {
    cursor: pointer;
    transition: r 0.2s ease;
}

.chart-line-point:hover {
    r: 8;
}

.chart-line-labels {
    display: flex;
    justify-content: flex-start;
    margin-top: 20px;
    min-width: fit-content;
}

.chart-line-labels-wrapper {
    position: relative;
}

.chart-line-container.centered .chart-line-labels {
    justify-content: center;
}

.chart-line-labels-centered {
    justify-content: center;
}

.chart-line-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0;
    flex-shrink: 0;
}

.chart-line-label-highlight {
    background: #8B0000;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    width: 100%;
}

.chart-line-label-text {
    font-size: 12px;
    font-weight: 500;
    color: #111;
    line-height: 1.4;
    word-wrap: break-word;
    overflow-wrap: break-word;
    margin-bottom: 4px;
    max-width: 100%;
}

.chart-line-label-percentage {
    font-size: 11px;
    color: #6b7280;
    font-weight: 600;
}

.chart-line-labels-enforcers .chart-line-label {
    padding-bottom: 20px;
}

.chart-line-enforcer-photo {
    width: 50px;
    height: 50px;
    margin: 0 auto 8px;
    position: relative;
}

.chart-line-enforcer-photo img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #8B0000;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.chart-line-enforcer-photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #8B0000, #C00000);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: 700;
    border: 3px solid #8B0000;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.chart-line-container.chart-enforcers {
    padding-bottom: 140px;
}

.chart-bars-vertical {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    justify-content: flex-start;
    min-height: 420px;
    padding: 90px 20px 20px 20px;
    min-width: fit-content;
}

/* Enforcer chart specific adjustments for profile pictures */
.chart-bars-vertical.chart-enforcers {
    min-height: 490px;
    padding: 170px 20px 20px 20px;
}

.chart-bars-vertical.centered {
    justify-content: center;
}

.chart-bars-vertical.show-all {
    padding-top: 130px;
}

.chart-bars-vertical.show-all.chart-enforcers {
    padding-top: 200px;
}

.chart-bars-vertical.show-all .chart-bar-wrapper-vertical {
    height: 140px !important;
}

.chart-bars-vertical.show-all .chart-bar-container-vertical {
    width: 100px;
    min-width: 90px;
    padding-top: 130px;
}

.chart-bars-vertical.show-all.chart-enforcers .chart-bar-container-vertical {
    padding-top: 200px;
}

.chart-bars-vertical.show-all .chart-bar-enforcer-photo {
    width: 50px;
    height: 50px;
}

.chart-bars-vertical.show-all .chart-bar-enforcer-photo-placeholder {
    font-size: 20px;
}

.chart-bars-vertical.show-all .chart-bar-label-vertical {
    height: 130px;
    font-size: 11px;
    line-height: 1.3;
}

.chart-bars-vertical.show-all .chart-bar-label-vertical-text {
    font-size: 10px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chart-bars-vertical.show-all .chart-bar-label-vertical-text-wrapper {
    margin-bottom: 6px;
}

.chart-bars-vertical.show-all .chart-bar-label-vertical-stats {
    margin-top: 4px;
}

.chart-bar-container-vertical {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 140px;
    min-width: 120px;
    position: relative;
    padding-top: 100px;
    gap: 4px;
}

/* Enforcer chart specific padding for profile pictures */
.chart-enforcers .chart-bar-container-vertical {
    padding-top: 170px;
}

.chart-bar-label-vertical {
    text-align: center;
    font-size: 13px;
    font-weight: 500;
    color: #111;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    position: absolute;
    top: 0;
}

.chart-bar-label-vertical-stats {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 8px;
}

.chart-bar-label-vertical-percentage {
    color: #6b7280;
    font-weight: 600;
    font-size: 12px;
}

.chart-bar-label-vertical-text-wrapper {
    width: 100%;
    margin-bottom: 8px;
}

.chart-bar-label-vertical-text {
    display: block;
    line-height: 1.4;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    font-size: 12px;
}

.chart-bar-wrapper-vertical {
    width: 100%;
    height: 280px;
    background: #f3f4f6;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    margin-top: 0;
}

.chart-bar-vertical {
    width: 100%;
    background: linear-gradient(180deg, #C00000, #8B0000);
    border-radius: 8px 8px 0 0;
    transition: height 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 4px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    min-height: 35px;
}

.chart-bar-vertical.highlighted {
    background: linear-gradient(180deg, #8B0000, #660000);
    box-shadow: 0 4px 8px rgba(139, 0, 0, 0.3);
}

.chart-bar-vertical.highlighted::after {
    content: '';
    position: absolute;
    top: -4px;
    left: 0;
    right: 0;
    height: 3px;
    background: #8B0000;
    border-radius: 8px 8px 0 0;
}

.chart-bar-enforcer-photo {
    width: 60px;
    height: 60px;
    margin: 0 auto 8px;
    position: relative;
}

.chart-bar-enforcer-photo img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #8B0000;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.chart-bar-enforcer-photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #8B0000, #C00000);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 700;
    border: 3px solid #8B0000;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.chart-bar-label-highlight {
    background: #8B0000;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    display: inline-block;
    text-align: center;
    width: 100%;
}

.chart-empty {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.chart-summary {
    margin-bottom: 20px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-summary-info {
    font-size: 14px;
    color: #6b7280;
}

.chart-summary-total {
    font-size: 16px;
    font-weight: 600;
    color: #111;
}
</style>

<script>
const modal = document.getElementById('detailsModal');
const closeBtn = document.getElementById('closeModal');
const modalBody = document.getElementById('modalBody');
const modalTitle = document.getElementById('modalTitle');

// Use relative URLs to avoid host mismatches when APP_URL differs
const endpoints = {
    tickets: '/admin/dashboard/tickets',
    users: '/admin/dashboard/users',
    pending: '/admin/dashboard/pending-tickets',
    period: '/admin/dashboard/period-reports',
    violations: '/admin/dashboard/violations-statistics',
};

function openModal(type) {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    
    // Show loading state
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <div class="spinner-border" role="status"></div>
            <p style="margin-top: 12px; color: #6b7280;">Loading...</p>
        </div>
    `;
    
    // Load appropriate data based on type
    let url = endpoints[type] || '';
    let title = '';
    
    switch(type) {
        case 'tickets':
            title = 'All Tickets';
            break;
        case 'users':
            title = 'All Users';
            break;
        case 'pending':
            title = 'Pending Issues (Unpaid Tickets)';
            break;
        case 'period':
            title = 'Ticket Report - Day / Week / Month';
            break;
        default:
            return;
    }
    
    // If endpoint not found, abort gracefully
    if (!url) {
        modalBody.innerHTML = '<div class="table-empty">No endpoint configured.</div>';
        return;
    }
    
    modalTitle.textContent = title;
    
    fetch(url, { credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) {
                throw new Error('Request failed with status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (type === 'period') {
                    renderPeriodReport(data.data);
                } else {
                    renderModalContent(type, data.data);
                }
            } else {
                modalBody.innerHTML = '<div class="table-empty">Error loading data.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="table-empty">Error loading data. Please try again.</div>';
        });
}

function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    modalBody.innerHTML = '';
}

closeBtn.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

function renderModalContent(type, data) {
    if (!data || data.length === 0) {
        modalBody.innerHTML = '<div class="table-empty">No data available.</div>';
        return;
    }
    
    let html = '<div style="overflow-x: auto;"><table class="details-table">';
    
    if (type === 'tickets' || type === 'pending') {
        html += `
            <thead>
                <tr>
                    <th>Citation #</th>
                    <th>Driver Name</th>
                    <th>Plate Number</th>
                    <th>Issued Date</th>
                    <th>Status</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        data.forEach(ticket => {
            const driverName = `${ticket.driver_firstname || ''} ${ticket.driver_middlename || ''} ${ticket.driver_lastname || ''}`.trim();
            const issuedDate = ticket.issued_date ? new Date(ticket.issued_date).toLocaleDateString() : '—';
            const status = ticket.status || 'Unpaid';
            const statusClass = status === 'Paid' ? 'status-paid' : 'status-unpaid';
            const price = ticket.price ? '₱' + parseFloat(ticket.price).toFixed(2) : '—';
            
            html += `
                <tr>
                    <td>${ticket.citation_number || '—'}</td>
                    <td>${driverName || '—'}</td>
                    <td>${ticket.plate_number || '—'}</td>
                    <td>${issuedDate}</td>
                    <td><span class="status-badge ${statusClass}">${status}</span></td>
                    <td>${price}</td>
                </tr>
            `;
        });
    } else if (type === 'users') {
        html += `
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>ID Number</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        data.forEach(user => {
            const roleClass = user.role.toLowerCase();
            const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString() : '—';
            
            html += `
                <tr>
                    <td>${user.fullname || '—'}</td>
                    <td>${user.username || '—'}</td>
                    <td><span class="role-badge role-${roleClass}">${user.role || '—'}</span></td>
                    <td>${user.id_number || '—'}</td>
                    <td>${user.gender ? user.gender.charAt(0).toUpperCase() + user.gender.slice(1) : '—'}</td>
                    <td>${user.contact_number || '—'}</td>
                    <td>${createdAt}</td>
                </tr>
            `;
        });
    }
    
    html += '</tbody></table></div>';
    modalBody.innerHTML = html;
}

function renderPeriodReport(data) {
    const today = data.today || { tickets: [], count: 0 };
    const week = data.week || { tickets: [], count: 0 };
    const month = data.month || { tickets: [], count: 0 };
    
    let html = `
        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('today')">Today (${today.count})</button>
            <button class="tab-button" onclick="switchTab('week')">This Week (${week.count})</button>
            <button class="tab-button" onclick="switchTab('month')">This Month (${month.count})</button>
        </div>
        
        <div id="tab-today" class="tab-content active">
            <div class="tab-content-header">Tickets Issued Today: ${today.count}</div>
            ${renderTicketTable(today.tickets)}
        </div>
        
        <div id="tab-week" class="tab-content">
            <div class="tab-content-header">Tickets Issued This Week: ${week.count}</div>
            ${renderTicketTable(week.tickets)}
        </div>
        
        <div id="tab-month" class="tab-content">
            <div class="tab-content-header">Tickets Issued This Month: ${month.count}</div>
            ${renderTicketTable(month.tickets)}
        </div>
    `;
    
    modalBody.innerHTML = html;
    
    // Store data for tab switching
    window.periodReportData = { today, week, month };
}

function renderTicketTable(tickets) {
    if (!tickets || tickets.length === 0) {
        return '<div class="table-empty">No tickets found for this period.</div>';
    }
    
    let html = '<div style="overflow-x: auto;"><table class="details-table">';
    html += `
        <thead>
            <tr>
                <th>Citation #</th>
                <th>Driver Name</th>
                <th>Plate Number</th>
                <th>Issued Date</th>
                <th>Status</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    tickets.forEach(ticket => {
        const driverName = `${ticket.driver_firstname || ''} ${ticket.driver_middlename || ''} ${ticket.driver_lastname || ''}`.trim();
        const issuedDate = ticket.issued_date ? new Date(ticket.issued_date).toLocaleDateString() : (ticket.created_at ? new Date(ticket.created_at).toLocaleDateString() : '—');
        const status = ticket.status || 'Unpaid';
        const statusClass = status === 'Paid' ? 'status-paid' : 'status-unpaid';
        const price = ticket.price ? '₱' + parseFloat(ticket.price).toFixed(2) : '—';
        
        html += `
            <tr>
                <td>${ticket.citation_number || '—'}</td>
                <td>${driverName || '—'}</td>
                <td>${ticket.plate_number || '—'}</td>
                <td>${issuedDate}</td>
                <td><span class="status-badge ${statusClass}">${status}</span></td>
                <td>${price}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    return html;
}

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById(`tab-${tabName}`);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

let currentViolationsFilter = 'all';
let currentViolationsData = null;
let showingAllViolations = false;
let currentChartType = 'violations';

function renderViolationsChart(violationsData, total, filter = 'all', containerId = 'violationsChartContainer', type = 'violations') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const hasData = violationsData && violationsData.length > 0;
    currentViolationsData = violationsData || [];
    currentViolationsFilter = filter;
    
    // Determine which violations to show (top 5 or all)
    const displayData = hasData ? (showingAllViolations ? violationsData : violationsData.slice(0, 5)) : [];
    const maxCount = hasData && violationsData.length > 0 ? violationsData[0].count : 0;
    
    // Get date range label
    const filterLabels = {
        'all': 'All Time',
        'today': 'Today',
        'week': 'This Week',
        'month': 'This Month',
        'custom': 'Custom Range'
    };
    
    let html = `
        <div class="chart-header-buttons">
            <button class="chart-header-btn ${type === 'violations' ? 'active' : ''}" onclick="switchChartType('violations', '${filter}')">
                Most Repeated Violations
            </button>
            <button class="chart-header-btn ${type === 'violators' ? 'active' : ''}" onclick="switchChartType('violators', '${filter}')">
                Most Repeated Violators
            </button>
            <button class="chart-header-btn ${type === 'enforcers' ? 'active' : ''}" onclick="switchChartType('enforcers', '${filter}')">
                Tomeco Enforcer Reports
            </button>
        </div>
        <div class="chart-container">
            <div class="chart-filters">
                <button class="chart-filter-btn ${filter === 'all' ? 'active' : ''}" onclick="changeViolationsFilter('all')">All</button>
                <button class="chart-filter-btn ${filter === 'today' ? 'active' : ''}" onclick="changeViolationsFilter('today')">Today</button>
                <button class="chart-filter-btn ${filter === 'week' ? 'active' : ''}" onclick="changeViolationsFilter('week')">Week</button>
                <button class="chart-filter-btn ${filter === 'month' ? 'active' : ''}" onclick="changeViolationsFilter('month')">Month</button>
                <button class="chart-filter-btn ${filter === 'custom' ? 'active' : ''}" onclick="changeViolationsFilter('custom')">Custom Range</button>
                <div id="customDateRange" class="chart-date-range" style="display: ${filter === 'custom' ? 'flex' : 'none'};">
                    <input type="date" id="startDate" class="chart-date-input" placeholder="Start Date">
                    <span style="color: #6b7280;">to</span>
                    <input type="date" id="endDate" class="chart-date-input" placeholder="End Date">
                    <button class="chart-filter-btn" onclick="applyCustomDateRange()" style="padding: 6px 12px;">Apply</button>
                </div>
            </div>
            
            <div class="chart-summary">
                <div class="chart-summary-info">
                    <div style="font-weight: 600; color: #111; margin-bottom: 4px;">${filterLabels[filter] || 'All Time'}</div>
                    <div style="font-size: 13px;">${hasData ? `Showing ${displayData.length} of ${violationsData.length} violations` : 'No violations found'}</div>
                </div>
                <div class="chart-summary-total">Total: ${hasData ? (total || violationsData.reduce((sum, v) => sum + v.count, 0)) : 0} violations</div>
            </div>
            
            <div class="chart-show-all">
                <label>
                    <input type="checkbox" id="showAllViolations" ${showingAllViolations ? 'checked' : ''} onchange="toggleShowAllViolations()" ${!hasData ? 'disabled' : ''}>
                    Show All Violations ${hasData ? (showingAllViolations ? `(${violationsData.length} total)` : '(currently showing top 5)') : '(no data available)'}
                </label>
            </div>
            
            <div class="chart-line-wrapper-outer">
                <div class="chart-line-container chart-violations ${showingAllViolations ? 'show-all' : 'centered'}" id="violationsChartContent">
    `;
    
    if (!hasData) {
        html += `
                    <div class="chart-empty" style="width: 100%; text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px;">
                        No violations data available for the selected period.
                    </div>
        `;
    } else {
        // Calculate dimensions
        const chartHeight = 300;
        const chartPadding = { top: 40, right: 40, bottom: 80, left: 60 };
        const plotHeight = chartHeight - chartPadding.top - chartPadding.bottom;
        const pointSpacing = showingAllViolations ? 100 : 150;
        // When showing top 5 (centered), use exact width for the data area
        // When showing all, use full width with proper spacing
        const dataAreaWidth = displayData.length * pointSpacing;
        const chartWidth = showingAllViolations 
            ? Math.max(600, dataAreaWidth + chartPadding.left + chartPadding.right)
            : dataAreaWidth + chartPadding.left + chartPadding.right;
        const plotWidth = chartWidth - chartPadding.left - chartPadding.right;
        
        // Generate Y-axis labels
        const yAxisSteps = 5;
        const yAxisLabels = [];
        for (let i = 0; i <= yAxisSteps; i++) {
            const value = Math.round((maxCount / yAxisSteps) * (yAxisSteps - i));
            yAxisLabels.push(value);
        }
        
        // Build line path and data points
        const points = [];
        let pathData = '';
        
        displayData.forEach((item, index) => {
            const x = chartPadding.left + (index * pointSpacing);
            const yRatio = maxCount > 0 ? (item.count / maxCount) : 0;
            const y = chartPadding.top + (plotHeight * (1 - yRatio));
            points.push({ x, y, item, index });
            
            if (index === 0) {
                pathData = `M ${x} ${y}`;
            } else {
                pathData += ` L ${x} ${y}`;
            }
        });
        
        html += `
                    <svg class="chart-line-svg ${showingAllViolations ? '' : 'chart-line-svg-centered'}" viewBox="0 0 ${chartWidth} ${chartHeight}" ${showingAllViolations ? 'preserveAspectRatio="none"' : 'preserveAspectRatio="xMidYMid meet"'} style="${showingAllViolations ? '' : 'max-width: ' + chartWidth + 'px; margin: 0 auto;'}">
                        <!-- Y-axis grid lines -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<line x1="${chartPadding.left}" y1="${y}" x2="${chartWidth - chartPadding.right}" y2="${y}" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="2,2"/>`;
                        }).join('')}
                        
                        <!-- Y-axis labels -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<text x="${chartPadding.left - 10}" y="${y + 4}" text-anchor="end" fill="#6b7280" font-size="12">${label}</text>`;
                        }).join('')}
                        
                        <!-- Line path -->
                        <path d="${pathData}" fill="none" stroke="#C00000" stroke-width="3" class="chart-line-path"/>
                        
                        <!-- Data points -->
                        ${points.map((point, index) => {
                            const isHighlighted = index === 0;
                            return `<circle cx="${point.x}" cy="${point.y}" r="${isHighlighted ? 7 : 5}" fill="${isHighlighted ? '#8B0000' : '#C00000'}" stroke="white" stroke-width="2" class="chart-line-point" data-index="${index}">
                                        <title>${point.item.violation}: ${point.item.count}</title>
                                    </circle>`;
                        }).join('')}
                        
                        <!-- Count labels above points -->
                        ${points.map((point, index) => {
                            return `<text x="${point.x}" y="${point.y - 15}" text-anchor="middle" fill="#C00000" font-size="12" font-weight="600">${point.item.count}</text>`;
                        }).join('')}
                    </svg>
                    
                    <!-- X-axis labels -->
                    <div class="chart-line-labels-wrapper" style="position: relative; width: ${chartWidth}px; margin: 20px auto 0 auto;">
                        ${displayData.map((item, index) => {
                            const violationPercentage = item.percentage || (total > 0 ? ((item.count / total) * 100).toFixed(1) : 0);
                            const isHighlighted = index === 0;
                            const labelX = chartPadding.left + (index * pointSpacing);
                            return `
                                <div class="chart-line-label" style="position: absolute; left: ${labelX - (pointSpacing / 2)}px; width: ${pointSpacing}px; transform: translateX(0);" title="${item.violation.replace(/"/g, '&quot;')}">
                                    ${isHighlighted ? '<div class="chart-line-label-highlight">TOP</div>' : ''}
                                    <div class="chart-line-label-text">${item.violation}</div>
                                    <div class="chart-line-label-percentage">${violationPercentage}%</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
        `;
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Animate line drawing if there's data
    if (hasData) {
        setTimeout(() => {
            const linePath = container.querySelector('.chart-line-path');
            if (linePath) {
                const pathLength = linePath.getTotalLength();
                linePath.style.strokeDasharray = pathLength;
                linePath.style.strokeDashoffset = pathLength;
                linePath.style.transition = 'stroke-dashoffset 1s ease-in-out';
                setTimeout(() => {
                    linePath.style.strokeDashoffset = 0;
                }, 100);
            }
            
            // Animate points
            container.querySelectorAll('.chart-line-point').forEach((point, index) => {
                point.style.opacity = '0';
                point.style.transition = 'opacity 0.3s ease-in-out';
                setTimeout(() => {
                    point.style.opacity = '1';
                }, 500 + (index * 100));
            });
        }, 100);
    }
}

function changeViolationsFilter(filter) {
    currentViolationsFilter = filter;
    showingAllViolations = false;
    
    // Update active button state
    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
        if (btn.textContent.trim() !== 'Apply') {
            btn.classList.remove('active');
        }
    });
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    if (filter === 'custom') {
        toggleCustomDateRange();
        // Set custom button as active if it exists
        const customBtn = Array.from(document.querySelectorAll('.chart-filter-btn')).find(btn => btn.textContent.trim() === 'Custom Range');
        if (customBtn) {
            customBtn.classList.add('active');
        }
        return;
    }
    
    switchChartType('violations', filter);
}

function toggleCustomDateRange() {
    const customRange = document.getElementById('customDateRange');
    if (customRange) {
        const isHidden = customRange.style.display === 'none' || !customRange.style.display;
        customRange.style.display = isHidden ? 'flex' : 'none';
    }
}

function applyCustomDateRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }
    
    const url = endpoints.violations + `?filter=custom&start_date=${startDate}&end_date=${endDate}`;
    
    const container = document.getElementById('violationsChartContainer');
    if (container) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading violations data...</p>
            </div>
        `;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderViolationsChart(data.data, data.total, 'custom', 'violationsChartContainer', 'violations');
            } else {
                if (container) {
                    container.innerHTML = '<div class="chart-empty">Error loading violations data.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (container) {
                container.innerHTML = '<div class="chart-empty">Error loading violations data. Please try again.</div>';
            }
        });
}

function toggleShowAllViolations() {
    showingAllViolations = document.getElementById('showAllViolations').checked;
    if (currentChartType === 'violations' && currentViolationsData) {
        const total = currentViolationsData.reduce((sum, v) => sum + v.count, 0);
        renderViolationsChart(currentViolationsData, total, currentViolationsFilter, 'violationsChartContainer', 'violations');
    } else if (currentChartType === 'violators') {
        // Fetch current violator data
        const url = '/admin/dashboard/violator-statistics' + `?filter=${currentViolationsFilter}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderViolatorChart(data.data, data.total, currentViolationsFilter, 'violationsChartContainer', 'violators');
                }
            });
    } else if (currentChartType === 'enforcers') {
        // Fetch current enforcer data
        const url = '/admin/dashboard/enforcer-statistics' + `?filter=${currentViolationsFilter}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEnforcerChart(data.data, data.total, currentViolationsFilter, 'violationsChartContainer', 'enforcers');
                }
            });
    }
}

function switchChartType(type, filter = 'all') {
    currentChartType = type;
    showingAllViolations = false;
    
    let url;
    if (type === 'violations') {
        url = endpoints.violations + `?filter=${filter}`;
    } else if (type === 'violators') {
        url = '/admin/dashboard/violator-statistics' + `?filter=${filter}`;
    } else {
        url = '/admin/dashboard/enforcer-statistics' + `?filter=${filter}`;
    }
    
    const container = document.getElementById('violationsChartContainer');
    if (container) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading chart data...</p>
            </div>
        `;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (type === 'violations') {
                    renderViolationsChart(data.data, data.total, filter, 'violationsChartContainer', 'violations');
                } else if (type === 'violators') {
                    renderViolatorChart(data.data, data.total, filter, 'violationsChartContainer', 'violators');
                } else {
                    renderEnforcerChart(data.data, data.total, filter, 'violationsChartContainer', 'enforcers');
                }
            } else {
                if (container) {
                    container.innerHTML = '<div class="chart-empty">Error loading chart data.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (container) {
                container.innerHTML = '<div class="chart-empty">Error loading chart data. Please try again.</div>';
            }
        });
}

function renderEnforcerChart(enforcersData, total, filter = 'all', containerId = 'violationsChartContainer', type = 'enforcers') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const hasData = enforcersData && enforcersData.length > 0;
    currentViolationsFilter = filter;
    currentChartType = type;
    
    // Determine which enforcers to show (top 5 or all)
    const displayData = hasData ? (showingAllViolations ? enforcersData : enforcersData.slice(0, 5)) : [];
    const maxCount = hasData && enforcersData.length > 0 ? enforcersData[0].count : 0;
    
    // Get date range label
    const filterLabels = {
        'all': 'All Time',
        'today': 'Today',
        'week': 'This Week',
        'month': 'This Month',
        'custom': 'Custom Range'
    };
    
    let html = `
        <div class="chart-header-buttons">
            <button class="chart-header-btn ${type === 'violations' ? 'active' : ''}" onclick="switchChartType('violations', '${filter}')">
                Most Repeated Violations
            </button>
            <button class="chart-header-btn ${type === 'violators' ? 'active' : ''}" onclick="switchChartType('violators', '${filter}')">
                Most Repeated Violators
            </button>
            <button class="chart-header-btn ${type === 'enforcers' ? 'active' : ''}" onclick="switchChartType('enforcers', '${filter}')">
                Tomeco Enforcer Reports
            </button>
        </div>
        <div class="chart-container">
            <div class="chart-filters">
                <button class="chart-filter-btn ${filter === 'all' ? 'active' : ''}" onclick="changeEnforcerFilter('all')">All</button>
                <button class="chart-filter-btn ${filter === 'today' ? 'active' : ''}" onclick="changeEnforcerFilter('today')">Today</button>
                <button class="chart-filter-btn ${filter === 'week' ? 'active' : ''}" onclick="changeEnforcerFilter('week')">Week</button>
                <button class="chart-filter-btn ${filter === 'month' ? 'active' : ''}" onclick="changeEnforcerFilter('month')">Month</button>
                <button class="chart-filter-btn ${filter === 'custom' ? 'active' : ''}" onclick="changeEnforcerFilter('custom')">Custom Range</button>
                <div id="customEnforcerDateRange" class="chart-date-range" style="display: ${filter === 'custom' ? 'flex' : 'none'};">
                    <input type="date" id="enforcerStartDate" class="chart-date-input" placeholder="Start Date">
                    <span style="color: #6b7280;">to</span>
                    <input type="date" id="enforcerEndDate" class="chart-date-input" placeholder="End Date">
                    <button class="chart-filter-btn" onclick="applyCustomEnforcerDateRange()" style="padding: 6px 12px;">Apply</button>
                </div>
            </div>
            
            <div class="chart-summary">
                <div class="chart-summary-info">
                    <div style="font-weight: 600; color: #111; margin-bottom: 4px;">${filterLabels[filter] || 'All Time'}</div>
                    <div style="font-size: 13px;">${hasData ? `Showing ${displayData.length} of ${enforcersData.length} enforcers` : 'No enforcers found'}</div>
                </div>
                <div class="chart-summary-total">Total: ${hasData ? (total || enforcersData.reduce((sum, v) => sum + v.count, 0)) : 0} tickets</div>
            </div>
            
            <div class="chart-show-all">
                <label>
                    <input type="checkbox" id="showAllEnforcers" ${showingAllViolations ? 'checked' : ''} onchange="toggleShowAllEnforcers()" ${!hasData ? 'disabled' : ''}>
                    Show All Enforcers ${hasData ? (showingAllViolations ? `(${enforcersData.length} total)` : '(currently showing top 5)') : '(no data available)'}
                </label>
            </div>
            
            <div class="chart-line-wrapper-outer">
                <div class="chart-line-container chart-enforcers ${showingAllViolations ? 'show-all' : 'centered'}" id="enforcersChartContent">
    `;
    
    if (!hasData) {
        html += `
                    <div class="chart-empty" style="width: 100%; text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px;">
                        No enforcer data available for the selected period.
                    </div>
        `;
    } else {
        // Calculate dimensions
        const chartHeight = 300;
        const chartPadding = { top: 40, right: 40, bottom: 120, left: 60 };
        const plotHeight = chartHeight - chartPadding.top - chartPadding.bottom;
        const pointSpacing = showingAllViolations ? 120 : 150;
        // When showing top 5 (centered), use exact width for the data area
        // When showing all, use full width with proper spacing
        const dataAreaWidth = displayData.length * pointSpacing;
        const chartWidth = showingAllViolations 
            ? Math.max(600, dataAreaWidth + chartPadding.left + chartPadding.right)
            : dataAreaWidth + chartPadding.left + chartPadding.right;
        const plotWidth = chartWidth - chartPadding.left - chartPadding.right;
        
        // Generate Y-axis labels
        const yAxisSteps = 5;
        const yAxisLabels = [];
        for (let i = 0; i <= yAxisSteps; i++) {
            const value = Math.round((maxCount / yAxisSteps) * (yAxisSteps - i));
            yAxisLabels.push(value);
        }
        
        // Build line path and data points
        const points = [];
        let pathData = '';
        
        displayData.forEach((item, index) => {
            const x = chartPadding.left + (index * pointSpacing);
            const yRatio = maxCount > 0 ? (item.count / maxCount) : 0;
            const y = chartPadding.top + (plotHeight * (1 - yRatio));
            points.push({ x, y, item, index });
            
            if (index === 0) {
                pathData = `M ${x} ${y}`;
            } else {
                pathData += ` L ${x} ${y}`;
            }
        });
        
        html += `
                    <svg class="chart-line-svg ${showingAllViolations ? '' : 'chart-line-svg-centered'}" viewBox="0 0 ${chartWidth} ${chartHeight}" ${showingAllViolations ? 'preserveAspectRatio="none"' : 'preserveAspectRatio="xMidYMid meet"'} style="${showingAllViolations ? '' : 'max-width: ' + chartWidth + 'px; margin: 0 auto;'}">
                        <!-- Y-axis grid lines -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<line x1="${chartPadding.left}" y1="${y}" x2="${chartWidth - chartPadding.right}" y2="${y}" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="2,2"/>`;
                        }).join('')}
                        
                        <!-- Y-axis labels -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<text x="${chartPadding.left - 10}" y="${y + 4}" text-anchor="end" fill="#6b7280" font-size="12">${label}</text>`;
                        }).join('')}
                        
                        <!-- Line path -->
                        <path d="${pathData}" fill="none" stroke="#C00000" stroke-width="3" class="chart-line-path"/>
                        
                        <!-- Data points -->
                        ${points.map((point, index) => {
                            const isHighlighted = index === 0;
                            return `<circle cx="${point.x}" cy="${point.y}" r="${isHighlighted ? 7 : 5}" fill="${isHighlighted ? '#8B0000' : '#C00000'}" stroke="white" stroke-width="2" class="chart-line-point" data-index="${index}">
                                        <title>${point.item.officer}: ${point.item.count}</title>
                                    </circle>`;
                        }).join('')}
                        
                        <!-- Count labels above points -->
                        ${points.map((point, index) => {
                            return `<text x="${point.x}" y="${point.y - 15}" text-anchor="middle" fill="#C00000" font-size="12" font-weight="600">${point.item.count}</text>`;
                        }).join('')}
                    </svg>
                    
                    <!-- X-axis labels with profile pictures -->
                    <div class="chart-line-labels-wrapper" style="position: relative; width: ${chartWidth}px; margin: 20px auto 0 auto;">
                        ${displayData.map((item, index) => {
                            const enforcerPercentage = item.percentage || (total > 0 ? ((item.count / total) * 100).toFixed(1) : 0);
                            const isHighlighted = index === 0;
                            const profilePicture = item.profile_picture || null;
                            const labelX = chartPadding.left + (index * pointSpacing);
                            return `
                                <div class="chart-line-label" style="position: absolute; left: ${labelX - (pointSpacing / 2)}px; width: ${pointSpacing}px; transform: translateX(0);" title="${item.officer.replace(/"/g, '&quot;')}">
                                    ${profilePicture ? `
                                        <div class="chart-line-enforcer-photo">
                                            <img src="${profilePicture}" alt="${item.officer}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="chart-line-enforcer-photo-placeholder" style="display: none;">
                                                ${item.officer.charAt(0).toUpperCase()}
                                            </div>
                                        </div>
                                    ` : `
                                        <div class="chart-line-enforcer-photo">
                                            <div class="chart-line-enforcer-photo-placeholder">
                                                ${item.officer.charAt(0).toUpperCase()}
                                            </div>
                                        </div>
                                    `}
                                    ${isHighlighted ? '<div class="chart-line-label-highlight">TOP</div>' : ''}
                                    <div class="chart-line-label-text">${item.officer}</div>
                                    <div class="chart-line-label-percentage">${enforcerPercentage}%</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
        `;
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Animate line drawing if there's data
    if (hasData) {
        setTimeout(() => {
            const linePath = container.querySelector('.chart-line-path');
            if (linePath) {
                const pathLength = linePath.getTotalLength();
                linePath.style.strokeDasharray = pathLength;
                linePath.style.strokeDashoffset = pathLength;
                linePath.style.transition = 'stroke-dashoffset 1s ease-in-out';
                setTimeout(() => {
                    linePath.style.strokeDashoffset = 0;
                }, 100);
            }
            
            // Animate points
            container.querySelectorAll('.chart-line-point').forEach((point, index) => {
                point.style.opacity = '0';
                point.style.transition = 'opacity 0.3s ease-in-out';
                setTimeout(() => {
                    point.style.opacity = '1';
                }, 500 + (index * 100));
            });
        }, 100);
    }
}

function renderViolatorChart(violatorsData, total, filter = 'all', containerId = 'violationsChartContainer', type = 'violators') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const hasData = violatorsData && violatorsData.length > 0;
    currentViolationsFilter = filter;
    currentChartType = type;
    
    // Determine which violators to show (top 5 or all)
    const displayData = hasData ? (showingAllViolations ? violatorsData : violatorsData.slice(0, 5)) : [];
    const maxCount = hasData && violatorsData.length > 0 ? violatorsData[0].count : 0;
    
    // Get date range label
    const filterLabels = {
        'all': 'All Time',
        'today': 'Today',
        'week': 'This Week',
        'month': 'This Month',
        'custom': 'Custom Range'
    };
    
    let html = `
        <div class="chart-header-buttons">
            <button class="chart-header-btn ${type === 'violations' ? 'active' : ''}" onclick="switchChartType('violations', '${filter}')">
                Most Repeated Violations
            </button>
            <button class="chart-header-btn ${type === 'violators' ? 'active' : ''}" onclick="switchChartType('violators', '${filter}')">
                Most Repeated Violators
            </button>
            <button class="chart-header-btn ${type === 'enforcers' ? 'active' : ''}" onclick="switchChartType('enforcers', '${filter}')">
                Tomeco Enforcer Reports
            </button>
        </div>
        <div class="chart-container">
            <div class="chart-filters">
                <button class="chart-filter-btn ${filter === 'all' ? 'active' : ''}" onclick="changeViolatorFilter('all')">All</button>
                <button class="chart-filter-btn ${filter === 'today' ? 'active' : ''}" onclick="changeViolatorFilter('today')">Today</button>
                <button class="chart-filter-btn ${filter === 'week' ? 'active' : ''}" onclick="changeViolatorFilter('week')">Week</button>
                <button class="chart-filter-btn ${filter === 'month' ? 'active' : ''}" onclick="changeViolatorFilter('month')">Month</button>
                <button class="chart-filter-btn ${filter === 'custom' ? 'active' : ''}" onclick="changeViolatorFilter('custom')">Custom Range</button>
                <div id="customViolatorDateRange" class="chart-date-range" style="display: ${filter === 'custom' ? 'flex' : 'none'};">
                    <input type="date" id="violatorStartDate" class="chart-date-input" placeholder="Start Date">
                    <span style="color: #6b7280;">to</span>
                    <input type="date" id="violatorEndDate" class="chart-date-input" placeholder="End Date">
                    <button class="chart-filter-btn" onclick="applyCustomViolatorDateRange()" style="padding: 6px 12px;">Apply</button>
                </div>
            </div>
            
            <div class="chart-summary">
                <div class="chart-summary-info">
                    <div style="font-weight: 600; color: #111; margin-bottom: 4px;">${filterLabels[filter] || 'All Time'}</div>
                    <div style="font-size: 13px;">${hasData ? `Showing ${displayData.length} of ${violatorsData.length} violators` : 'No violators found'}</div>
                </div>
                <div class="chart-summary-total">Total: ${hasData ? (total || violatorsData.reduce((sum, v) => sum + v.count, 0)) : 0} tickets</div>
            </div>
            
            <div class="chart-show-all">
                <label>
                    <input type="checkbox" id="showAllViolators" ${showingAllViolations ? 'checked' : ''} onchange="toggleShowAllViolators()" ${!hasData ? 'disabled' : ''}>
                    Show All Violators ${hasData ? (showingAllViolations ? `(${violatorsData.length} total)` : '(currently showing top 5)') : '(no data available)'}
                </label>
            </div>
            
            <div class="chart-line-wrapper-outer">
                <div class="chart-line-container chart-violations ${showingAllViolations ? 'show-all' : 'centered'}" id="violatorsChartContent">
    `;
    
    if (!hasData) {
        html += `
                    <div class="chart-empty" style="width: 100%; text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px;">
                        No violator data available for the selected period.
                    </div>
        `;
    } else {
        // Calculate dimensions
        const chartHeight = 300;
        const chartPadding = { top: 40, right: 40, bottom: 80, left: 60 };
        const plotHeight = chartHeight - chartPadding.top - chartPadding.bottom;
        const pointSpacing = showingAllViolations ? 100 : 150;
        // When showing top 5 (centered), use exact width for the data area
        // When showing all, use full width with proper spacing
        const dataAreaWidth = displayData.length * pointSpacing;
        const chartWidth = showingAllViolations 
            ? Math.max(600, dataAreaWidth + chartPadding.left + chartPadding.right)
            : dataAreaWidth + chartPadding.left + chartPadding.right;
        const plotWidth = chartWidth - chartPadding.left - chartPadding.right;
        
        // Generate Y-axis labels
        const yAxisSteps = 5;
        const yAxisLabels = [];
        for (let i = 0; i <= yAxisSteps; i++) {
            const value = Math.round((maxCount / yAxisSteps) * (yAxisSteps - i));
            yAxisLabels.push(value);
        }
        
        // Build line path and data points
        const points = [];
        let pathData = '';
        
        displayData.forEach((item, index) => {
            const x = chartPadding.left + (index * pointSpacing);
            const yRatio = maxCount > 0 ? (item.count / maxCount) : 0;
            const y = chartPadding.top + (plotHeight * (1 - yRatio));
            points.push({ x, y, item, index });
            
            if (index === 0) {
                pathData = `M ${x} ${y}`;
            } else {
                pathData += ` L ${x} ${y}`;
            }
        });
        
        html += `
                    <svg class="chart-line-svg ${showingAllViolations ? '' : 'chart-line-svg-centered'}" viewBox="0 0 ${chartWidth} ${chartHeight}" ${showingAllViolations ? 'preserveAspectRatio="none"' : 'preserveAspectRatio="xMidYMid meet"'} style="${showingAllViolations ? '' : 'max-width: ' + chartWidth + 'px; margin: 0 auto;'}">
                        <!-- Y-axis grid lines -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<line x1="${chartPadding.left}" y1="${y}" x2="${chartWidth - chartPadding.right}" y2="${y}" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="2,2"/>`;
                        }).join('')}
                        
                        <!-- Y-axis labels -->
                        ${yAxisLabels.map((label, i) => {
                            const y = chartPadding.top + (plotHeight * (i / yAxisSteps));
                            return `<text x="${chartPadding.left - 10}" y="${y + 4}" text-anchor="end" fill="#6b7280" font-size="12">${label}</text>`;
                        }).join('')}
                        
                        <!-- Line path -->
                        <path d="${pathData}" fill="none" stroke="#C00000" stroke-width="3" class="chart-line-path"/>
                        
                        <!-- Data points -->
                        ${points.map((point, index) => {
                            const isHighlighted = index === 0;
                            return `<circle cx="${point.x}" cy="${point.y}" r="${isHighlighted ? 7 : 5}" fill="${isHighlighted ? '#8B0000' : '#C00000'}" stroke="white" stroke-width="2" class="chart-line-point" data-index="${index}">
                                        <title>${point.item.violator}: ${point.item.count}</title>
                                    </circle>`;
                        }).join('')}
                        
                        <!-- Count labels above points -->
                        ${points.map((point, index) => {
                            return `<text x="${point.x}" y="${point.y - 15}" text-anchor="middle" fill="#C00000" font-size="12" font-weight="600">${point.item.count}</text>`;
                        }).join('')}
                    </svg>
                    
                    <!-- X-axis labels -->
                    <div class="chart-line-labels-wrapper" style="position: relative; width: ${chartWidth}px; margin: 20px auto 0 auto;">
                        ${displayData.map((item, index) => {
                            const violatorPercentage = item.percentage || (total > 0 ? ((item.count / total) * 100).toFixed(1) : 0);
                            const isHighlighted = index === 0;
                            const labelX = chartPadding.left + (index * pointSpacing);
                            return `
                                <div class="chart-line-label" style="position: absolute; left: ${labelX - (pointSpacing / 2)}px; width: ${pointSpacing}px; transform: translateX(0);" title="${item.violator.replace(/"/g, '&quot;')}">
                                    ${isHighlighted ? '<div class="chart-line-label-highlight">TOP</div>' : ''}
                                    <div class="chart-line-label-text">${item.violator}</div>
                                    <div class="chart-line-label-percentage">${violatorPercentage}%</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
        `;
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Animate line drawing if there's data
    if (hasData) {
        setTimeout(() => {
            const linePath = container.querySelector('.chart-line-path');
            if (linePath) {
                const pathLength = linePath.getTotalLength();
                linePath.style.strokeDasharray = pathLength;
                linePath.style.strokeDashoffset = pathLength;
                linePath.style.transition = 'stroke-dashoffset 1s ease-in-out';
                setTimeout(() => {
                    linePath.style.strokeDashoffset = 0;
                }, 100);
            }
            
            // Animate points
            container.querySelectorAll('.chart-line-point').forEach((point, index) => {
                point.style.opacity = '0';
                point.style.transition = 'opacity 0.3s ease-in-out';
                setTimeout(() => {
                    point.style.opacity = '1';
                }, 500 + (index * 100));
            });
        }, 100);
    }
}

function changeViolatorFilter(filter) {
    currentViolationsFilter = filter;
    showingAllViolations = false;
    
    // Update active button state
    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
        if (btn.textContent.trim() !== 'Apply') {
            btn.classList.remove('active');
        }
    });
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    if (filter === 'custom') {
        const customRange = document.getElementById('customViolatorDateRange');
        if (customRange) {
            customRange.style.display = customRange.style.display === 'none' ? 'flex' : 'none';
        }
        const customBtn = Array.from(document.querySelectorAll('.chart-filter-btn')).find(btn => btn.textContent.trim() === 'Custom Range');
        if (customBtn) {
            customBtn.classList.add('active');
        }
        return;
    }
    
    switchChartType('violators', filter);
}

function applyCustomViolatorDateRange() {
    const startDate = document.getElementById('violatorStartDate').value;
    const endDate = document.getElementById('violatorEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }
    
    const url = '/admin/dashboard/violator-statistics' + `?filter=custom&start_date=${startDate}&end_date=${endDate}`;
    
    const container = document.getElementById('violationsChartContainer');
    if (container) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading violator data...</p>
            </div>
        `;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderViolatorChart(data.data, data.total, 'custom', 'violationsChartContainer', 'violators');
            } else {
                if (container) {
                    container.innerHTML = '<div class="chart-empty">Error loading violator data.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (container) {
                container.innerHTML = '<div class="chart-empty">Error loading violator data. Please try again.</div>';
            }
        });
}

function toggleShowAllViolators() {
    showingAllViolations = document.getElementById('showAllViolators').checked;
    // Fetch current violator data
    const url = '/admin/dashboard/violator-statistics' + `?filter=${currentViolationsFilter}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderViolatorChart(data.data, data.total, currentViolationsFilter, 'violationsChartContainer', 'violators');
            }
        });
}

function changeEnforcerFilter(filter) {
    currentViolationsFilter = filter;
    showingAllViolations = false;
    
    // Update active button state
    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
        if (btn.textContent.trim() !== 'Apply') {
            btn.classList.remove('active');
        }
    });
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    if (filter === 'custom') {
        const customRange = document.getElementById('customEnforcerDateRange');
        if (customRange) {
            customRange.style.display = customRange.style.display === 'none' ? 'flex' : 'none';
        }
        const customBtn = Array.from(document.querySelectorAll('.chart-filter-btn')).find(btn => btn.textContent.trim() === 'Custom Range');
        if (customBtn) {
            customBtn.classList.add('active');
        }
        return;
    }
    
    switchChartType('enforcers', filter);
}

function applyCustomEnforcerDateRange() {
    const startDate = document.getElementById('enforcerStartDate').value;
    const endDate = document.getElementById('enforcerEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }
    
    switchChartType('enforcers', 'custom');
    
    const url = '/admin/dashboard/enforcer-statistics' + `?filter=custom&start_date=${startDate}&end_date=${endDate}`;
    
    const container = document.getElementById('violationsChartContainer');
    if (container) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading chart data...</p>
            </div>
        `;
    }
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderEnforcerChart(data.data, data.total, 'custom', 'violationsChartContainer', 'enforcers');
            } else {
                if (container) {
                    container.innerHTML = '<div class="chart-empty">Error loading chart data.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (container) {
                container.innerHTML = '<div class="chart-empty">Error loading chart data. Please try again.</div>';
            }
        });
}

function toggleShowAllEnforcers() {
    showingAllViolations = document.getElementById('showAllEnforcers').checked;
    // Reload the current chart type
    if (currentChartType === 'enforcers') {
        const url = '/admin/dashboard/enforcer-statistics' + `?filter=${currentViolationsFilter}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEnforcerChart(data.data, data.total, currentViolationsFilter, 'violationsChartContainer', 'enforcers');
                }
            });
    } else {
        if (currentViolationsData) {
            const total = currentViolationsData.reduce((sum, v) => sum + v.count, 0);
            renderViolationsChart(currentViolationsData, total, currentViolationsFilter, 'violationsChartContainer', 'violations');
        }
    }
}

// Load violations chart on page load
document.addEventListener('DOMContentLoaded', function() {
    const url = endpoints.violations + '?filter=all';
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderViolationsChart(data.data, data.total, 'all', 'violationsChartContainer', 'violations');
            } else {
                const container = document.getElementById('violationsChartContainer');
                if (container) {
                    container.innerHTML = '<div class="chart-empty">Error loading violations data.</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const container = document.getElementById('violationsChartContainer');
            if (container) {
                container.innerHTML = '<div class="chart-empty">Error loading violations data. Please try again.</div>';
            }
        });
});
</script>
@endsection

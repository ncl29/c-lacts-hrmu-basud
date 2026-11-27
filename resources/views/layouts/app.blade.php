<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - C-LACTS HRMU Basud</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                        mono: ['Inter', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                        serif: ['Inter', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                    },
                    fontWeight: {
                        thin: '300',
                        extralight: '300',
                        light: '300',
                        normal: '400',
                        medium: '500',
                        semibold: '500',
                        bold: '500',
                        extrabold: '500',
                        black: '500',
                    },
                    colors: {
                        gov: {
                            50: '#f0f5fc',
                            100: '#e1ecf9',
                            200: '#c5daf2',
                            300: '#99bfe8',
                            400: '#679edb',
                            500: '#3c79c8',
                            600: '#1b4d8f', // Formal Executive Blue
                            700: '#143c72', // Formal Philippine Navy
                            800: '#10305c',
                            900: '#0a1e3b', // Deep Archival Navy
                            navy: '#081426', // Stately Municipal Obsidian Navy
                            gold: '#c2943b', // Republic Antique Gold
                            goldLight: '#fdf8ee',
                            goldBorder: '#e6caa0',
                            emerald: '#0d6347',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap');
        
        /* Baseline font scale (14px standard enterprise system sizing) */
        html {
            font-size: 14px;
            transition: font-size 0.12s ease-in-out;
        }
        html.text-size-standard,
        html.text-size-normal {
            font-size: 14px;
        }
        html.text-size-large {
            font-size: 16px;
        }
        html.text-size-xl {
            font-size: 18px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 400;
            color: #1e293b;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        strong, b, h1, h2, h3, h4, h5, h6, th {
            font-weight: 500;
        }

        .font-mono, code, pre, kbd, samp {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-variant-numeric: tabular-nums;
        }

        /* High Contrast Accessibility Mode */
        html.high-contrast-mode body,
        html.senior-vision-mode body {
            color: #020617 !important;
            font-weight: 500 !important;
            background-color: #f8fafc !important;
        }
        html.high-contrast-mode .text-slate-400,
        html.high-contrast-mode .text-slate-500,
        html.senior-vision-mode .text-slate-400,
        html.senior-vision-mode .text-slate-500 {
            color: #334155 !important;
            font-weight: 600 !important;
        }
        html.high-contrast-mode .text-slate-600,
        html.senior-vision-mode .text-slate-600 {
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        html.high-contrast-mode .text-slate-700,
        html.high-contrast-mode .text-slate-800,
        html.high-contrast-mode .text-slate-900,
        html.senior-vision-mode .text-slate-700,
        html.senior-vision-mode .text-slate-800,
        html.senior-vision-mode .text-slate-900 {
            color: #000000 !important;
            font-weight: 800 !important;
        }
        html.high-contrast-mode .border-slate-100,
        html.high-contrast-mode .border-slate-200,
        html.high-contrast-mode .border-slate-300,
        html.senior-vision-mode .border-slate-200,
        html.senior-vision-mode .border-slate-300 {
            border-color: #475569 !important;
            border-width: 1.5px !important;
        }
        html.high-contrast-mode table th,
        html.senior-vision-mode table th {
            color: #000000 !important;
            font-weight: 800 !important;
            background-color: #e2e8f0 !important;
            border-bottom: 2px solid #0f172a !important;
            font-size: 0.85rem !important;
        }
        html.high-contrast-mode table td,
        html.senior-vision-mode table td {
            color: #000000 !important;
            font-weight: 600 !important;
            padding-top: 0.875rem !important;
            padding-bottom: 0.875rem !important;
            border-bottom: 1.5px solid #cbd5e1 !important;
        }
        html.high-contrast-mode input,
        html.high-contrast-mode select,
        html.high-contrast-mode textarea,
        html.senior-vision-mode input,
        html.senior-vision-mode select,
        html.senior-vision-mode textarea {
            color: #000000 !important;
            font-weight: 700 !important;
            border: 2px solid #334155 !important;
            background-color: #ffffff !important;
        }
        html.high-contrast-mode button,
        html.high-contrast-mode a.inline-flex,
        html.senior-vision-mode button,
        html.senior-vision-mode a.inline-flex {
            font-weight: 800 !important;
            border-width: 1.5px !important;
            letter-spacing: 0.01em !important;
        }
        html.high-contrast-mode i.ph,
        html.senior-vision-mode i.ph {
            stroke-width: 2px;
        }

        /* Simplified Layout Mode */
        html.simplified-layout-mode .shadow-sm,
        html.simplified-layout-mode .shadow-md,
        html.simplified-layout-mode .shadow-lg,
        html.simplified-layout-mode .shadow-xl,
        html.simplified-layout-mode .shadow-2xl {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
        }
        html.simplified-layout-mode table th {
            background-color: #f1f5f9 !important;
            letter-spacing: normal !important;
            text-transform: none !important;
            font-size: 0.875rem !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
        html.simplified-layout-mode table td {
            padding-top: 0.875rem !important;
            padding-bottom: 0.875rem !important;
            line-height: 1.6 !important;
        }
        html.simplified-layout-mode input,
        html.simplified-layout-mode select,
        html.simplified-layout-mode textarea {
            box-shadow: none !important;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 0.5rem !important;
        }

        /* Collapsible Desktop Sidebar for Wide Tables */
        @media (min-width: 768px) {
            #sidePanel {
                transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.18s ease-in-out;
            }
            html.sidebar-collapsed #sidePanel {
                transform: translateX(-100%) !important;
                pointer-events: none !important;
            }
            html.sidebar-collapsed #mainContentColumn {
                margin-left: 0 !important;
            }
        }

        #sidePanel {
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }
        #sidePanel::-webkit-scrollbar {
            width: 4px;
        }
        #sidePanel::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidePanel::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
    </style>

    <!-- Immediate script to prevent layout shift of accessibility settings -->
    <script>
        (function() {
            try {
                let size = localStorage.getItem('clacts_font_size') || 'standard';
                if (size === 'normal') size = 'standard';

                if (size === 'xl') document.documentElement.classList.add('text-size-xl');
                else if (size === 'large') document.documentElement.classList.add('text-size-large');
                else document.documentElement.classList.add('text-size-standard');

                if (localStorage.getItem('clacts_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col text-slate-800 antialiased selection:bg-amber-500 selection:text-white bg-slate-100/60 overflow-x-clip">

    <!-- Top GovTech Official Header (Sticky) -->
    <header class="sticky top-0 z-40 bg-gov-navy text-white border-b border-slate-800/90 shadow-md">
        <!-- Republic Micro-Tricolor Line -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-700 via-amber-400 to-rose-600"></div>

        <div class="w-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-2.5 sm:py-3">
            <div class="flex items-center justify-between gap-2 sm:gap-4">
                <!-- Branding with Official Municipal Seal -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    @auth
                        <!-- Mobile navigation side panel toggle -->
                        <button type="button" onclick="toggleSidePanel()" class="md:hidden p-2 min-h-[40px] min-w-[40px] flex items-center justify-center rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer shrink-0" aria-label="Toggle navigation side panel">
                            <i class="ph ph-list text-xl"></i>
                        </button>
                        <!-- Desktop collapsible sidebar button to maximize table workspace -->
                        <button type="button" onclick="toggleDesktopSidebar()" id="btnDesktopSidebarToggle" class="hidden md:flex p-2 min-h-[40px] min-w-[40px] items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent hover:border-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer shrink-0 transition" aria-label="Toggle sidebar width" title="Collapse sidebar to maximize table workspace">
                            <i class="ph ph-sidebar-simple text-xl" id="desktopSidebarIcon"></i>
                        </button>
                    @endauth

                    <a href="{{ Auth::check() && Auth::user()->role === 'hrmu_staff' ? route('hr.leave-records.index') : (Auth::check() && Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::check() && Auth::user()->role === 'routing_clerk' ? route('clerk.scanner') : route('employee.dashboard'))) }}" class="flex items-center gap-2.5 sm:gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 rounded-lg min-w-0 flex-1">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white p-0.5 sm:p-1 ring-2 ring-amber-400/40 border border-amber-500/50 shadow-sm shrink-0 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/basud-logo.png') }}" alt="Seal of the Municipality of Basud" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] sm:text-[11px] uppercase tracking-wider text-amber-300 font-bold leading-tight truncate hidden min-[400px]:block">Republic of the Philippines • Region V</div>
                            <div class="text-sm sm:text-base md:text-lg font-black tracking-tight text-white flex items-center gap-1.5 sm:gap-2 min-w-0">
                                <span class="truncate">Basud<span class="hidden min-[380px]:inline"> Municipality</span></span>
                                <span class="text-amber-300 text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded bg-amber-400/15 border border-amber-400/30 shrink-0">HRMU</span>
                            </div>
                            <div class="text-xs text-slate-300 hidden lg:block font-medium truncate">Centralized Leave Application & Credit Tracking System (C-LACTS)</div>
                        </div>
                    </a>
                </div>

                <!-- Header Right Controls: Employee Info & Accessibility Settings -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- User Profile Badge (Name & Position) -->
                    @auth
                        <div class="flex items-center gap-2 sm:gap-3 pr-2 sm:pr-3 border-r border-slate-700/80">
                            <!-- Mobile simplified avatar / badge -->
                            <a href="{{ route('profile.show') }}" class="sm:hidden flex items-center justify-center w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-amber-300 font-bold text-sm hover:bg-slate-700 transition shrink-0" title="My Profile ({{ Auth::user()->username }})">
                                {{ strtoupper(substr(Auth::user()->employee->first_name ?? Auth::user()->username, 0, 1)) }}
                            </a>
                            <a href="{{ route('profile.show') }}" class="text-right hidden sm:block group hover:opacity-90 transition rounded-lg p-1 -m-1 focus:outline-none focus:ring-1 focus:ring-amber-400 max-w-[200px] md:max-w-[240px] lg:max-w-xs" title="View & Edit My Profile">
                                <div class="text-xs md:text-sm font-bold text-white flex items-center justify-end gap-1.5 md:gap-2 group-hover:text-amber-300 transition">
                                    <span class="truncate">{{ Auth::user()->employee->first_name ?? Auth::user()->username }} {{ Auth::user()->employee->last_name ?? '' }}</span>
                                    @if(Auth::user()->role === 'admin')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-400/40 shrink-0">ADMIN</span>
                                    @elseif(Auth::user()->role === 'hrmu_staff')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-500/15 text-sky-300 border border-sky-400/40 shrink-0">HR STAFF</span>
                                    @elseif(Auth::user()->role === 'routing_clerk')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-400/40 shrink-0">RECEIVER</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700 shrink-0">EMPLOYEE</span>
                                    @endif
                                    <i class="ph ph-gear text-xs text-slate-400 group-hover:text-amber-300 transition shrink-0"></i>
                                </div>
                                <div class="text-[11px] md:text-xs text-slate-300 font-medium truncate leading-snug">{{ Auth::user()->employee->office->office_name ?? 'Basud Municipal Office' }}</div>
                            </a>
                        </div>
                    @else
                        <div class="pr-2 sm:pr-3 border-r border-slate-700">
                            <a href="{{ route('login') }}" class="px-3 sm:px-4 py-2 rounded-lg text-xs font-bold bg-gov-700 hover:bg-gov-800 text-white transition flex items-center gap-1.5 sm:gap-2 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 min-h-[40px]">
                                <i class="ph ph-sign-in text-base"></i>
                                <span>Sign In</span>
                            </a>
                        </div>
                    @endauth

                    <!-- Accessibility Settings Dropdown -->
                    <div class="relative shrink-0" id="accessibilityDropdownContainer">
                        <button 
                            type="button" 
                            id="btnAccessibilityToggle" 
                            onclick="toggleAccessibilityDropdown()" 
                            aria-expanded="false" 
                            aria-haspopup="true" 
                            class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-2 rounded-xl bg-slate-900/90 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-700/80 shadow-xs text-xs font-semibold transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[40px]" 
                            title="Accessibility and Display Settings"
                        >
                            <i class="ph-bold ph-sliders-horizontal text-base sm:text-lg text-amber-400 shrink-0" aria-hidden="true"></i>
                            <span class="hidden sm:inline font-sans font-medium text-xs">Accessibility</span>
                            <i class="ph ph-caret-down text-xs text-slate-400 transition-transform duration-150" id="accessibilityCaretIcon"></i>
                        </button>

                        <!-- Dropdown Menu Panel -->
                        <div 
                            id="accessibilityDropdownPanel" 
                            role="dialog"
                            aria-label="Accessibility & Display Settings"
                            class="hidden absolute right-0 top-full mt-2 w-72 sm:w-80 max-w-[calc(100vw-24px)] bg-white text-slate-900 rounded-2xl shadow-2xl border border-slate-200 z-50 p-4 space-y-3 focus:outline-none"
                        >
                            <!-- Panel Header -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono">Accessibility Settings</h3>
                                <button 
                                    type="button" 
                                    onclick="closeAccessibilityDropdown()" 
                                    class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                                    aria-label="Close accessibility settings"
                                >
                                    <i class="ph ph-x text-sm"></i>
                                </button>
                            </div>

                            <!-- Text Size Segmented Selector -->
                            <div class="grid grid-cols-3 gap-1 p-1 bg-slate-100 rounded-xl border border-slate-200/80" role="group" aria-label="Text Size Options">
                                <button 
                                    type="button" 
                                    onclick="setAccessibilityTextSize('standard')" 
                                    id="optTextStandard" 
                                    class="py-1.5 px-2 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                >
                                    Standard
                                </button>
                                <button 
                                    type="button" 
                                    onclick="setAccessibilityTextSize('large')" 
                                    id="optTextLarge" 
                                    class="py-1.5 px-2 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                >
                                    Large
                                </button>
                                <button 
                                    type="button" 
                                    onclick="setAccessibilityTextSize('xl')" 
                                    id="optTextXl" 
                                    class="py-1.5 px-2 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                >
                                    Extra Large
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- App Body Layout (Side Panel + Main Content) -->
    <div class="flex-1 flex min-h-0">
        @auth
            <!-- Mobile Overlay Backdrop -->
            <div id="sidePanelBackdrop" onclick="toggleSidePanel()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-30 hidden md:hidden"></div>

            <!-- Side Panel (Desktop Fixed + Mobile Off-Canvas: Stationary while screen scrolls) -->
            <aside id="sidePanel" class="fixed top-0 md:top-16 bottom-0 left-0 z-50 md:z-30 w-72 bg-slate-900 text-slate-200 border-r border-slate-800 shrink-0 flex flex-col justify-between transition-transform duration-200 ease-in-out -translate-x-full md:translate-x-0 h-full md:h-[calc(100vh-64px)] overflow-hidden">
                
                <!-- Mobile Close Bar -->
                <div class="md:hidden flex items-center justify-between p-3 border-b border-slate-800 bg-slate-950 shrink-0">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest font-mono">Navigation Menu</span>
                    <button type="button" onclick="toggleSidePanel()" class="text-slate-300 hover:text-white p-1 rounded-lg cursor-pointer">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Items -->
                <nav class="p-3 pt-4 sm:pt-5 space-y-1 sm:space-y-1.5 flex-1 overflow-y-auto flex flex-col justify-start">
                    <!-- Section Label: Employee Portal (Hidden for pure Routing Clerks and HRMU Staff) -->
                    @if(!in_array(Auth::user()->role, ['hrmu_staff', 'routing_clerk']))
                        <div class="px-2.5 pt-1 pb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Employee Services</span>
                        </div>

                        <a href="{{ route('employee.dashboard') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('employee.dashboard') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('employee.dashboard') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-squares-four text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Self-Service</div>
                            </div>
                        </a>

                        <a href="{{ route('employee.leave-record') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('employee.leave-record*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('employee.leave-record*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-notebook text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">My Leave Record</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Physical Workflow (QR Scanner for Clerk, HR Staff, Admin, Dept Head, and Mayor) -->
                    @if(Auth::user()->canAccessQrScanner())
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Document Routing</span>
                        </div>

                        <a href="{{ route('clerk.scanner') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('clerk.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}" title="Unified QR barcode scanning and document custody terminal">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('clerk.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-qr-code text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">QR Document Scanner</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: HR Management -->
                    @if(in_array(Auth::user()->role, ['hrmu_staff', 'admin']))
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">HR Administration</span>
                        </div>

                        <a href="{{ route('hr.leave-records.index') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.leave-records.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.leave-records.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-identification-badge text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Leave Records</div>
                            </div>
                        </a>

                        <a href="{{ route('hr.tardiness.create') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.tardiness.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.tardiness.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-calculator text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">HR Starla Engine</div>
                            </div>
                        </a>

                        <a href="{{ route('hr.starla.tables') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.starla.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.starla.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-table text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Starla Tables</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Administration -->
                    @if(Auth::user()->role === 'admin')
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">System Oversight</span>
                        </div>

                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('admin.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-shield-star text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Admin Control</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Account & Settings (Placed last, accessible to all authenticated personnel) -->
                    <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Account & Settings</span>
                    </div>

                    <a href="{{ route('profile.show') }}" class="px-3 py-2.5 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}" title="View and edit personal details and change password">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('profile.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                            <i class="ph-bold ph-user-gear text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1 leading-tight">
                            <div class="truncate">My Profile & Settings</div>
                        </div>
                    </a>
                </nav>

                <!-- Sidebar Bottom: Account Actions (Sign Out Only) -->
                <div class="p-2.5 sm:p-3 border-t border-slate-800 bg-slate-950/90 shrink-0">
                    <button 
                        type="button" 
                        onclick="openLogoutModal()" 
                        class="w-full py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 hover:text-rose-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-400 min-h-[36px]"
                        title="Sign out of C-LACTS terminal"
                        aria-haspopup="dialog"
                    >
                        <i class="ph-bold ph-sign-out text-base"></i>
                        <span>SIGN OUT</span>
                    </button>
                </div>
            </aside>
        @endauth

        <!-- Main Content Area Column (Offset by stationary sidebar width on desktop) -->
        <div id="mainContentColumn" class="flex-1 flex flex-col min-w-0 bg-slate-50 md:ml-72 transition-[margin] duration-200 ease-in-out">
            <!-- Centered Cancellation Confirmation Floating Dialog Modal -->
            @if(session('cancellation_success') || (session('success') && str_contains(session('success'), 'has been successfully cancelled')) || (session('success') && str_contains(session('success'), 'has been cancelled within the 7-working-day period')))
                @php
                    $cancellationMsg = session('cancellation_success') ?? session('success');
                    $cancelledRef = session('cancelled_reference_code') ?? (preg_match('/(LV-\d{4}-\d{4})/', $cancellationMsg, $matches) ? $matches[1] : null);
                    $wasApproved = session('cancellation_was_approved') ?? str_contains($cancellationMsg, 'Approved Leave Application');
                @endphp
                <div 
                    id="cancellationNoticeModal" 
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="cancellationModalTitle"
                    class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto transition-all duration-300 animate-in fade-in"
                    onclick="if(event.target === this) closeCancellationModal()"
                >
                    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border-t-4 border-t-rose-500 border-x border-b border-slate-200 overflow-hidden transform animate-in zoom-in-95 duration-200 my-auto">
                        <!-- Top Header -->
                        <div class="shrink-0 bg-slate-50 px-5 sm:px-6 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                            <button 
                                type="button" 
                                onclick="closeCancellationModal()" 
                                class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-500"
                                aria-label="Close dialog"
                            >
                                <i class="ph ph-x text-lg"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="overflow-y-auto flex-1 min-h-0 p-5 sm:p-8 space-y-6">
                            <!-- Large Cancellation Icon & Title -->
                            <div class="text-center space-y-3">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-100 text-rose-600 border-4 border-rose-200/80 shadow-md">
                                    <i class="ph-bold ph-check-circle text-4xl"></i>
                                </div>
                                <div>
                                    <h3 id="cancellationModalTitle" class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                        Cancellation Completed
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium leading-relaxed">
                                        {{ $cancellationMsg }}
                                    </p>
                                </div>
                            </div>

                            <!-- Cancellation Status Summary Card -->
                            <div class="rounded-2xl bg-slate-50 border border-slate-200/90 p-4 space-y-3 text-xs">
                                <div class="flex items-center justify-between pb-2.5 border-b border-slate-200 text-slate-500 font-mono text-[11px]">
                                    <span class="uppercase tracking-wider">Status Record</span>
                                    <span class="text-rose-700 font-bold bg-rose-50 px-2.5 py-0.5 rounded border border-rose-200 uppercase">CANCELLED</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    @if($cancelledRef)
                                        <div>
                                            <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Reference Number</div>
                                            <div class="text-xs font-bold font-mono text-slate-900 mt-0.5">
                                                {{ $cancelledRef }}
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Routing Status</div>
                                        <div class="text-xs font-bold text-slate-900 mt-0.5">
                                            Terminated / Invalidated
                                        </div>
                                    </div>
                                    @if($wasApproved)
                                        <div class="sm:col-span-2 bg-amber-50 border border-amber-200 p-2.5 rounded-xl text-amber-900 text-[11px] leading-snug flex items-start gap-2">
                                            <i class="ph-bold ph-arrow-u-up-left text-base text-amber-700 shrink-0 mt-0.5"></i>
                                            <span><strong>Credits Restored:</strong> Deducted leave credits have been returned to your balance.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="shrink-0 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                onclick="closeCancellationModal()" 
                                class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white text-xs sm:text-sm font-bold shadow-md transition cursor-pointer flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-slate-400 min-h-[42px]"
                            >
                                <span>Acknowledge & Close</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Centered Success Receipt Confirmation Modal (Dims background, persists until user closes) -->
            @if(session('login_success') || (session('success') && str_contains(session('success'), 'Welcome')))
                <div 
                    id="loginSuccessModal" 
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="successModalTitle"
                    class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto transition-all duration-300"
                    onclick="if(event.target === this) closeLoginSuccessModal()"
                >
                    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 my-auto">
                        <!-- Top Header with Close Button -->
                        <div class="shrink-0 bg-slate-50 px-5 sm:px-6 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                            <button 
                                type="button" 
                                onclick="closeLoginSuccessModal()" 
                                class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                aria-label="Close dialog"
                            >
                                <i class="ph ph-x text-lg"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="overflow-y-auto flex-1 min-h-0 p-5 sm:p-8 space-y-6">
                            <!-- Large Checkmark Medallion & Title -->
                            <div class="text-center space-y-3">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 border-4 border-emerald-200/80 shadow-md">
                                    <i class="ph-bold ph-check text-4xl"></i>
                                </div>
                                <div>
                                    <h3 id="successModalTitle" class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                        Authentication Successful
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">
                                        {{ session('login_success') ?? session('success') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="shrink-0 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                            <button 
                                type="button" 
                                onclick="closeLoginSuccessModal()" 
                                class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white text-xs sm:text-sm font-bold shadow-md transition cursor-pointer flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[42px]"
                            >
                                <span>Continue</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Centered Operational Feedback Dialogue Box (Success, Error, Warning, Info) -->
            @php
                $showSuccessDialog = session('success') && !session('login_success') && !session('recalculate_success') && !session('cancellation_success') && !str_contains(session('success'), 'Welcome') && !str_contains(session('success'), 'cancelled');
                $feedbackType = null;
                $feedbackTitle = '';
                $feedbackMessage = '';
                $feedbackErrors = [];

                if ($errors->any()) {
                    $feedbackType = 'error';
                    $feedbackTitle = 'Please Correct the Following Errors';
                    $feedbackErrors = $errors->all();
                } elseif (session('error')) {
                    $feedbackType = 'error';
                    $feedbackTitle = 'Operational Notice';
                    $feedbackMessage = session('error');
                } elseif (session('warning')) {
                    $feedbackType = 'warning';
                    $feedbackTitle = 'Notice';
                    $feedbackMessage = session('warning');
                } elseif ($showSuccessDialog) {
                    $feedbackType = 'success';
                    $feedbackTitle = 'Success';
                    $feedbackMessage = session('success');
                } elseif (session('info')) {
                    $feedbackType = 'info';
                    $feedbackTitle = 'Information';
                    $feedbackMessage = session('info');
                }

                $feedbackStyles = [
                    'success' => [
                        'borderTop' => 'border-t-emerald-500',
                        'iconWrap'  => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                        'icon'      => 'ph-bold ph-check-circle',
                        'ring'      => 'focus:ring-emerald-500',
                        'btn'       => 'bg-gov-700 hover:bg-gov-800 focus:ring-gov-600',
                    ],
                    'error' => [
                        'borderTop' => 'border-t-rose-500',
                        'iconWrap'  => 'bg-rose-100 text-rose-600 border-rose-200',
                        'icon'      => 'ph-bold ph-warning-circle',
                        'ring'      => 'focus:ring-rose-500',
                        'btn'       => 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500',
                    ],
                    'warning' => [
                        'borderTop' => 'border-t-amber-500',
                        'iconWrap'  => 'bg-amber-100 text-amber-600 border-amber-200',
                        'icon'      => 'ph-bold ph-warning',
                        'ring'      => 'focus:ring-amber-500',
                        'btn'       => 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500',
                    ],
                    'info' => [
                        'borderTop' => 'border-t-gov-600',
                        'iconWrap'  => 'bg-gov-100 text-gov-700 border-gov-200',
                        'icon'      => 'ph-bold ph-info',
                        'ring'      => 'focus:ring-gov-600',
                        'btn'       => 'bg-gov-700 hover:bg-gov-800 focus:ring-gov-600',
                    ],
                ];
            @endphp

            @if($feedbackType)
                @php
                    $st = $feedbackStyles[$feedbackType];
                @endphp
                <div
                    id="sessionFeedbackModal"
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="sessionFeedbackTitle"
                    class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto transition-all duration-300"
                    onclick="if(event.target === this) closeSessionFeedbackModal()"
                >
                    <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border-t-4 {{ $st['borderTop'] }} border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 my-auto" onclick="event.stopPropagation()">
                        <div class="shrink-0 bg-slate-50 px-5 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                            <button
                                type="button"
                                onclick="closeSessionFeedbackModal()"
                                class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 {{ $st['ring'] }}"
                                aria-label="Close dialog"
                            >
                                <i class="ph ph-x text-lg"></i>
                            </button>
                        </div>

                        <div class="overflow-y-auto flex-1 min-h-0 p-6 space-y-4 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full border-4 shadow-xs {{ $st['iconWrap'] }}">
                                <i class="{{ $st['icon'] }} text-3xl"></i>
                            </div>

                            <div class="space-y-1.5">
                                <h3 id="sessionFeedbackTitle" class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                    {{ $feedbackTitle }}
                                </h3>

                                @if(count($feedbackErrors) > 0)
                                    <ul class="list-disc list-inside text-left text-xs sm:text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 mt-2 space-y-1">
                                        @foreach($feedbackErrors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs sm:text-sm text-slate-600 font-medium leading-relaxed">
                                        {{ $feedbackMessage }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                            <button
                                type="button"
                                onclick="closeSessionFeedbackModal()"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer flex items-center justify-center min-h-10 focus:outline-none focus:ring-2 {{ $st['btn'] }}"
                            >
                                OK, Got It
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Content Area -->
            <main id="clactsLiveMain" class="flex-1 max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6">
                <div id="clactsPageContent">
                    @yield('content')
                </div>

                <!-- Navigation Skeleton UI (Shown immediately when switching pages) -->
                <div id="clactsNavSkeleton" class="hidden space-y-6 animate-pulse" aria-hidden="true">
                    <!-- Header Skeleton -->
                    <div class="bg-white rounded-xl p-4 sm:p-6 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-11 h-11 rounded-xl bg-slate-200 shrink-0"></div>
                            <div class="space-y-2 flex-1 max-w-md">
                                <div class="h-3 w-28 bg-slate-200 rounded"></div>
                                <div class="h-5 w-56 bg-slate-200 rounded"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-10 w-28 bg-slate-200 rounded-lg"></div>
                            <div class="h-10 w-32 bg-slate-200 rounded-lg"></div>
                        </div>
                    </div>

                    <!-- KPI Cards Skeleton -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        @for($s = 0; $s < 4; $s++)
                            <div class="bg-white rounded-xl p-4 sm:p-5 border border-slate-200 shadow-xs space-y-3">
                                <div class="h-3 w-24 bg-slate-200 rounded"></div>
                                <div class="h-7 w-20 bg-slate-200 rounded"></div>
                                <div class="h-2.5 w-32 bg-slate-100 rounded"></div>
                            </div>
                        @endfor
                    </div>

                    <!-- Table Card Skeleton -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                            <div class="h-4 w-44 bg-slate-200 rounded"></div>
                            <div class="h-6 w-24 bg-slate-100 rounded-lg"></div>
                        </div>
                        <div class="p-4 sm:p-5 space-y-3">
                            @for($r = 0; $r < 5; $r++)
                                <div class="flex items-center justify-between gap-4 py-2 border-b border-slate-100 last:border-b-0">
                                    <div class="h-3.5 w-28 bg-slate-200 rounded"></div>
                                    <div class="h-3.5 w-40 bg-slate-200 rounded hidden sm:block"></div>
                                    <div class="h-3.5 w-24 bg-slate-100 rounded"></div>
                                    <div class="h-6 w-20 bg-slate-200 rounded-full"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </main>

            <!-- GovTech System Footer -->
            <footer class="bg-white border-t border-slate-200 mt-auto py-3.5 sm:py-4 text-xs text-slate-500">
                <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-center gap-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <img src="{{ asset('images/basud-logo.png') }}" alt="Basud Seal" class="w-5 h-5 object-contain">
                        <span>© {{ date('Y') }} <strong>Municipal Government of Basud, Camarines Norte</strong> — Human Resource Management Unit (HRMU)</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Side Panel Controller Script -->
    <script>
        function toggleSidePanel() {
            const panel = document.getElementById('sidePanel');
            const backdrop = document.getElementById('sidePanelBackdrop');
            if (panel && backdrop) {
                const isClosed = panel.classList.contains('-translate-x-full');
                if (isClosed) {
                    panel.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden', 'md:overflow-auto');
                } else {
                    panel.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
                }
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAccessibilityDropdown();
                const logoutModal = document.getElementById('logoutConfirmModal');
                if (logoutModal && !logoutModal.classList.contains('hidden')) {
                    closeLogoutModal();
                    return;
                }
                const panel = document.getElementById('sidePanel');
                const backdrop = document.getElementById('sidePanelBackdrop');
                if (panel && backdrop && !panel.classList.contains('-translate-x-full')) {
                    panel.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
                }
            }
        });

        // Close Accessibility dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('accessibilityDropdownContainer');
            if (container && !container.contains(e.target)) {
                closeAccessibilityDropdown();
            }
        });

        // Logout Confirmation Dialog Controls
        function openLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                const cancelBtn = document.getElementById('btnCancelLogout');
                if (cancelBtn) {
                    cancelBtn.focus();
                }
            }
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Accessibility Settings Dropdown Controls
        function toggleAccessibilityDropdown() {
            const panel = document.getElementById('accessibilityDropdownPanel');
            const caret = document.getElementById('accessibilityCaretIcon');
            const btn = document.getElementById('btnAccessibilityToggle');
            if (!panel) return;

            const isHidden = panel.classList.contains('hidden');
            if (isHidden) {
                panel.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (caret) caret.classList.add('rotate-180');
                syncAccessibilityUI();
            } else {
                closeAccessibilityDropdown();
            }
        }

        function closeAccessibilityDropdown() {
            const panel = document.getElementById('accessibilityDropdownPanel');
            const caret = document.getElementById('accessibilityCaretIcon');
            const btn = document.getElementById('btnAccessibilityToggle');
            if (panel && !panel.classList.contains('hidden')) {
                panel.classList.add('hidden');
                if (btn) btn.setAttribute('aria-expanded', 'false');
                if (caret) caret.classList.remove('rotate-180');
            }
        }

        // Global Text Size Adjustment (Standard 16px, Large 18.5px, Extra Large 21px)
        function setAccessibilityTextSize(size, save = true) {
            if (size === 'normal') size = 'standard';
            document.documentElement.classList.remove('text-size-standard', 'text-size-normal', 'text-size-large', 'text-size-xl');

            const btnStd = document.getElementById('optTextStandard');
            const btnLrg = document.getElementById('optTextLarge');
            const btnXl = document.getElementById('optTextXl');
            const label = document.getElementById('currentTextSizeLabel');

            const resetBtn = (b) => {
                if (b) {
                    b.classList.remove('bg-gov-700', 'text-white', 'shadow-xs');
                    b.classList.add('text-slate-600', 'hover:text-slate-900');
                }
            };
            const activeBtn = (b) => {
                if (b) {
                    b.classList.add('bg-gov-700', 'text-white', 'shadow-xs');
                    b.classList.remove('text-slate-600', 'hover:text-slate-900');
                }
            };

            [btnStd, btnLrg, btnXl].forEach(resetBtn);

            if (size === 'xl') {
                document.documentElement.classList.add('text-size-xl');
                activeBtn(btnXl);
                if (label) label.textContent = 'Extra Large (130%)';
            } else if (size === 'large') {
                document.documentElement.classList.add('text-size-large');
                activeBtn(btnLrg);
                if (label) label.textContent = 'Large (115%)';
            } else {
                document.documentElement.classList.add('text-size-standard');
                activeBtn(btnStd);
                if (label) label.textContent = 'Standard (100%)';
            }

            if (save) {
                try {
                    localStorage.setItem('clacts_font_size', size);
                } catch (e) {}
            }
        }

        // High Contrast Mode Toggle
        function toggleHighContrastSetting() {
            const isHigh = document.documentElement.classList.toggle('high-contrast-mode');
            if (isHigh) {
                document.documentElement.classList.add('senior-vision-mode');
            } else {
                document.documentElement.classList.remove('senior-vision-mode');
            }
            try {
                localStorage.setItem('clacts_high_contrast', isHigh ? 'true' : 'false');
                localStorage.setItem('clacts_senior_mode', isHigh ? 'true' : 'false');
            } catch (e) {}
            syncHighContrastUI(isHigh);
        }

        function syncHighContrastUI(isHigh) {
            const btn = document.getElementById('toggleHighContrastBtn');
            const indicator = document.getElementById('toggleHighContrastIndicator');
            if (!btn || !indicator) return;

            btn.setAttribute('aria-checked', isHigh ? 'true' : 'false');
            if (isHigh) {
                btn.classList.add('bg-amber-500');
                btn.classList.remove('bg-slate-300');
                indicator.classList.add('translate-x-[20px]');
                indicator.classList.remove('translate-x-0');
            } else {
                btn.classList.remove('bg-amber-500');
                btn.classList.add('bg-slate-300');
                indicator.classList.remove('translate-x-[20px]');
                indicator.classList.add('translate-x-0');
            }
        }

        // Simplified Layout Mode Toggle
        function toggleSimplifiedLayoutSetting() {
            const isSimplified = document.documentElement.classList.toggle('simplified-layout-mode');
            try {
                localStorage.setItem('clacts_simplified_layout', isSimplified ? 'true' : 'false');
            } catch (e) {}
            syncSimplifiedLayoutUI(isSimplified);
        }

        function syncSimplifiedLayoutUI(isSimplified) {
            const btn = document.getElementById('toggleSimplifiedLayoutBtn');
            const indicator = document.getElementById('toggleSimplifiedLayoutIndicator');
            if (!btn || !indicator) return;

            btn.setAttribute('aria-checked', isSimplified ? 'true' : 'false');
            if (isSimplified) {
                btn.classList.add('bg-amber-500');
                btn.classList.remove('bg-slate-300');
                indicator.classList.add('translate-x-[20px]');
                indicator.classList.remove('translate-x-0');
            } else {
                btn.classList.remove('bg-amber-500');
                btn.classList.add('bg-slate-300');
                indicator.classList.remove('translate-x-[20px]');
                indicator.classList.add('translate-x-0');
            }
        }

        // Reset All Display Settings to Standard Defaults
        function resetAccessibilitySettings() {
            setAccessibilityTextSize('standard', true);
            
            document.documentElement.classList.remove('high-contrast-mode', 'senior-vision-mode');
            try {
                localStorage.setItem('clacts_high_contrast', 'false');
                localStorage.setItem('clacts_senior_mode', 'false');
            } catch (e) {}
            syncHighContrastUI(false);

            document.documentElement.classList.remove('simplified-layout-mode');
            try {
                localStorage.setItem('clacts_simplified_layout', 'false');
            } catch (e) {}
            syncSimplifiedLayoutUI(false);
        }

        // Synchronize all accessibility controls with the current HTML class state
        function syncAccessibilityUI() {
            const size = localStorage.getItem('clacts_font_size') || 'standard';
            setAccessibilityTextSize(size, false);

            const isHigh = document.documentElement.classList.contains('high-contrast-mode') || document.documentElement.classList.contains('senior-vision-mode');
            syncHighContrastUI(isHigh);

            const isSimplified = document.documentElement.classList.contains('simplified-layout-mode');
            syncSimplifiedLayoutUI(isSimplified);
        }

        // Collapsible Desktop Sidebar for Wide Screen Table Workspace
        function toggleDesktopSidebar() {
            const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            try {
                localStorage.setItem('clacts_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            } catch (e) {}
            updateDesktopSidebarUI(isCollapsed);
        }

        function updateDesktopSidebarUI(isCollapsed) {
            const btn = document.getElementById('btnDesktopSidebarToggle');
            const icon = document.getElementById('desktopSidebarIcon');
            if (!btn) return;

            if (isCollapsed) {
                btn.classList.add('bg-amber-500/20', 'text-amber-300', 'border-amber-400/40');
                btn.classList.remove('text-slate-300', 'border-transparent');
                btn.setAttribute('title', 'Expand sidebar navigation');
                btn.setAttribute('aria-label', 'Expand sidebar navigation');
            } else {
                btn.classList.remove('bg-amber-500/20', 'text-amber-300', 'border-amber-400/40');
                btn.classList.add('text-slate-300', 'border-transparent');
                btn.setAttribute('title', 'Collapse sidebar to maximize table workspace');
                btn.setAttribute('aria-label', 'Collapse sidebar to maximize table workspace');
            }
        }

        // Change Password Modal Handlers
        function openChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    const firstInput = document.getElementById('current_password');
                    if (firstInput) firstInput.focus();
                }, 100);
            }
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function toggleInputType(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('ph-eye');
                    icon.classList.add('ph-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('ph-eye-slash');
                    icon.classList.add('ph-eye');
                }
            }
        }

        // Cancellation Success Modal Close Handler (Persists until user clicks Close or Backdrop)
        function closeCancellationModal() {
            const modal = document.getElementById('cancellationNoticeModal');
            if (modal) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    modal.remove();
                }, 250);
            }
        }

        // Login Success Modal Close Handler (Persists until user clicks Close or Backdrop)
        function closeLoginSuccessModal() {
            const modal = document.getElementById('loginSuccessModal');
            if (modal) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    modal.remove();
                }, 250);
            }
        }

        // Operational Feedback Modal Close Handler
        function closeSessionFeedbackModal() {
            const modal = document.getElementById('sessionFeedbackModal');
            if (modal) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    modal.remove();
                }, 250);
            }
        }

        // Refresh Completed Dialog Close Handler
        function closeRefreshCompletedModal() {
            const modal = document.getElementById('refreshCompletedModal');
            if (modal) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    modal.remove();
                }, 250);
            }
        }

        // Trigger Refresh Loading Animation Overlay
        function showRefreshLoadingOverlay() {
            const overlay = document.getElementById('refreshOverlayModal');
            if (overlay && overlay.classList.contains('hidden')) {
                overlay.classList.remove('hidden');
            }
        }

        // Global Refresh & Reload Event Handling
        (function() {
            let isNormalNavigation = false;

            // Flag when the user clicks any link or anchor
            document.addEventListener('click', function(e) {
                const targetAnchor = e.target.closest('a');
                if (targetAnchor && targetAnchor.getAttribute('href') && !targetAnchor.getAttribute('href').startsWith('#')) {
                    isNormalNavigation = true;
                }
            }, true);

            // Flag when any form is submitted
            document.addEventListener('submit', function() {
                isNormalNavigation = true;
            }, true);

            // 1. Strictly detect Ctrl + R / Cmd + R
            window.addEventListener('keydown', function(e) {
                if ((e.key === 'r' || e.key === 'R') && (e.ctrlKey || e.metaKey)) {
                    sessionStorage.setItem('clacts_explicit_refresh', 'true');
                    showRefreshLoadingOverlay();
                }
            });

            // 2. Strictly detect browser refresh button/icon (window reload)
            window.addEventListener('beforeunload', function() {
                // If it is NOT a normal link navigation and NOT a form submission, it was triggered by the browser refresh button / reload
                if (!isNormalNavigation) {
                    sessionStorage.setItem('clacts_explicit_refresh', 'true');
                    showRefreshLoadingOverlay();
                }
            });
        })();

        // Synchronize all states upon initial DOM load
        document.addEventListener('DOMContentLoaded', function() {
            try {
                syncAccessibilityUI();
                const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
                updateDesktopSidebarUI(isCollapsed);

                // Detect if current load was strictly triggered by Ctrl+R or clicking the refresh button
                const navEntry = window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0];
                const isNavigationReload = navEntry ? navEntry.type === 'reload' : (window.performance && window.performance.navigation && window.performance.navigation.type === 1);
                const wasExplicitlyRefreshed = sessionStorage.getItem('clacts_explicit_refresh') === 'true';

                // Only show dialog box if it was explicitly triggered via Ctrl+R or refresh button
                if (wasExplicitlyRefreshed && isNavigationReload) {
                    sessionStorage.removeItem('clacts_explicit_refresh');
                    const completedModal = document.getElementById('refreshCompletedModal');
                    if (completedModal) {
                        completedModal.classList.remove('hidden');
                    }
                } else {
                    // Clean up flag if navigation was not a reload
                    sessionStorage.removeItem('clacts_explicit_refresh');
                }

                // Allow closing modals with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeLoginSuccessModal();
                        closeSessionFeedbackModal();
                        closeCancellationModal();
                        closeRefreshCompletedModal();
                    }
                });

                // Navigation Skeleton UI on internal link clicks
                function showNavSkeleton() {
                    const content = document.getElementById('clactsPageContent');
                    const skeleton = document.getElementById('clactsNavSkeleton');
                    if (content && skeleton) {
                        content.classList.add('hidden');
                        skeleton.classList.remove('hidden');
                    }
                }

                function hideNavSkeleton() {
                    const content = document.getElementById('clactsPageContent');
                    const skeleton = document.getElementById('clactsNavSkeleton');
                    if (content && skeleton) {
                        skeleton.classList.add('hidden');
                        content.classList.remove('hidden');
                    }
                }

                document.addEventListener('click', function(e) {
                    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                    const link = e.target.closest('a[href]');
                    if (!link) return;
                    if (link.hasAttribute('download') || link.hasAttribute('data-no-skeleton')) return;
                    if (link.target && link.target !== '_self') return;

                    const href = link.getAttribute('href') || '';
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin !== window.location.origin) return;
                        if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash) return;
                    } catch (_) {
                        return;
                    }

                    // Close mobile drawer if open before showing skeleton
                    const panel = document.getElementById('sidePanel');
                    const backdrop = document.getElementById('sidePanelBackdrop');
                    if (panel && !panel.classList.contains('-translate-x-full') && window.innerWidth < 768) {
                        panel.classList.add('-translate-x-full');
                        if (backdrop) backdrop.classList.add('hidden');
                    }

                    showNavSkeleton();
                });

                window.addEventListener('pageshow', hideNavSkeleton);

                // Logout Form Submission Loader
                const logoutForm = document.getElementById('logoutForm');
                const btnConfirmLogout = document.getElementById('btnConfirmLogout');
                const logoutOverlayModal = document.getElementById('logoutOverlayModal');
                const logoutConfirmModal = document.getElementById('logoutConfirmModal');

                if (logoutForm && btnConfirmLogout) {
                    logoutForm.addEventListener('submit', function() {
                        btnConfirmLogout.classList.add('pointer-events-none', 'opacity-80');
                        if (logoutConfirmModal) {
                            logoutConfirmModal.classList.add('hidden');
                        }
                        if (logoutOverlayModal) {
                            logoutOverlayModal.classList.remove('hidden');
                        }
                    });
                }

                window.showClactsToast = function(message, type = 'info') {
                    const colors = {
                        success: 'bg-emerald-900 border-emerald-500 text-white',
                        warning: 'bg-amber-900 border-amber-400 text-white',
                        error: 'bg-rose-900 border-rose-500 text-white',
                        info: 'bg-slate-900 border-slate-600 text-white'
                    };
                    let container = document.getElementById('clactsGlobalToastContainer');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'clactsGlobalToastContainer';
                        container.className = 'fixed bottom-4 right-4 z-[120] flex flex-col gap-2 max-w-sm w-[calc(100vw-32px)] pointer-events-none';
                        document.body.appendChild(container);
                    }
                    const toast = document.createElement('div');
                    toast.className = `pointer-events-auto px-4 py-3 rounded-xl border-l-4 shadow-xl text-xs sm:text-sm font-medium transition-opacity duration-200 ${colors[type] || colors.info}`;
                    toast.innerText = message;
                    container.appendChild(toast);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 220);
                    }, 4200);
                };

                // 1-Second Fingerprint Live Sync (polls lightweight version hash, only refreshes DOM when DB changes)
                @auth
                (function initClactsLiveSync() {
                    const LIVE_SYNC_INTERVAL_MS = 1000;
                    let isChecking = false;
                    let lastVersion = null;
                    let pendingDomSync = false;

                    function isUserBusy() {
                        if (document.visibilityState !== 'visible') return true;
                        if (document.querySelector('.fixed.inset-0:not(.hidden)')) return true;
                        const active = document.activeElement;
                        const mainEl = document.getElementById('clactsLiveMain');
                        if (active && mainEl && mainEl.contains(active) && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)) {
                            return true;
                        }
                        if (typeof html5QrCode !== 'undefined' && html5QrCode && html5QrCode.isScanning) {
                            return true;
                        }
                        if (window.getSelection && window.getSelection().toString().length > 0) {
                            return true;
                        }
                        return false;
                    }

                    let syncAbortController = null;
                    let isNavigatingAway = false;

                    window.addEventListener('beforeunload', function() {
                        isNavigatingAway = true;
                        if (syncAbortController) {
                            try { syncAbortController.abort(); } catch (e) {}
                        }
                    });

                    document.addEventListener('click', function(e) {
                        const anchor = e.target && e.target.closest ? e.target.closest('a[href]') : null;
                        if (anchor && anchor.getAttribute('target') !== '_blank' && !anchor.getAttribute('href').startsWith('#') && !anchor.hasAttribute('download')) {
                            if (syncAbortController) {
                                try { syncAbortController.abort(); } catch (err) {}
                            }
                        }
                    }, true);

                    async function refreshPageDom() {
                        const currentMain = document.getElementById('clactsLiveMain');
                        if (!currentMain || isNavigatingAway) return;

                        syncAbortController = new AbortController();
                        const res = await fetch(window.location.href, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            },
                            credentials: 'same-origin',
                            cache: 'no-store',
                            signal: syncAbortController.signal
                        });

                        if (!res.ok || res.redirected) return;
                        const html = await res.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const newMain = doc.getElementById('clactsLiveMain');
                        if (!newMain) return;

                        // On QR Document Scanner, only update Recent Scans list so active previews/inputs are untouched
                        const currentScans = document.getElementById('recentScansList');
                        const newScans = doc.getElementById('recentScansList');
                        if (currentScans && newScans) {
                            if (currentScans.innerHTML !== newScans.innerHTML) {
                                currentScans.innerHTML = newScans.innerHTML;
                            }
                            return;
                        }

                        if (currentMain.innerHTML === newMain.innerHTML) return;

                        // Preserve active UI state before swapping content
                        const savedScrollY = window.scrollY;
                        let activeAdminTab = null;
                        for (const [btnId, tabKey] of [['tabBtnUsers', 'users'], ['tabBtnStarla', 'starla'], ['tabBtnAudit', 'audit'], ['tabBtnBackups', 'backups']]) {
                            const btn = document.getElementById(btnId);
                            if (btn && btn.classList.contains('border-gov-700')) {
                                activeAdminTab = tabKey;
                                break;
                            }
                        }

                        const isCscView = document.getElementById('originalLedgerView') && !document.getElementById('originalLedgerView').classList.contains('hidden');
                        let activeYear = null;
                        document.querySelectorAll('.year-tab-btn').forEach(btn => {
                            if (btn.classList.contains('bg-white')) {
                                activeYear = btn.id.replace('yearTabBtn_', '');
                            }
                        });

                        const remindersEl = document.getElementById('remindersCardsList');
                        const remindersHidden = remindersEl ? remindersEl.classList.contains('hidden') : false;

                        currentMain.innerHTML = newMain.innerHTML;

                        if (activeAdminTab && typeof switchAdminTab === 'function') {
                            switchAdminTab(activeAdminTab);
                        }
                        if (isCscView && typeof toggleRecordView === 'function') {
                            toggleRecordView();
                        }
                        if (activeYear && typeof switchYear === 'function') {
                            switchYear(activeYear);
                        }
                        if (remindersHidden && typeof toggleRemindersCollapse === 'function') {
                            toggleRemindersCollapse();
                        }

                        window.scrollTo(0, savedScrollY);
                    }

                    async function checkLiveSyncVersion() {
                        if (isChecking || isNavigatingAway || document.visibilityState !== 'visible') {
                            if (!isNavigatingAway) scheduleNextSync(LIVE_SYNC_INTERVAL_MS);
                            return;
                        }
                        isChecking = true;
                        const startedAt = performance.now();

                        try {
                            syncAbortController = new AbortController();
                            const res = await fetch("{{ route('api.live-sync') }}", {
                                method: 'GET',
                                headers: { 'Accept': 'application/json' },
                                credentials: 'same-origin',
                                cache: 'no-store',
                                signal: syncAbortController.signal
                            });

                            if (!res.ok || res.redirected) return;
                            const payload = await res.json();
                            if (!payload || !payload.version) return;

                            if (lastVersion === null) {
                                lastVersion = payload.version;
                                return;
                            }

                            if (payload.version !== lastVersion) {
                                lastVersion = payload.version;
                                pendingDomSync = true;

                                // If Routing Timeline modal is open, refresh it immediately even while open
                                if (typeof window.refreshActiveTrackerModal === 'function') {
                                    window.refreshActiveTrackerModal();
                                }
                            }

                            if (pendingDomSync && !isUserBusy()) {
                                pendingDomSync = false;
                                await refreshPageDom();
                            }
                        } catch (err) {
                            // Ignore transient network errors during background sync
                        } finally {
                            const elapsed = performance.now() - startedAt;
                            isChecking = false;
                            // 1s polling on fast servers (Render); back off slightly on high-latency localhost so clicks aren't queued
                            const nextDelay = elapsed < 400 ? LIVE_SYNC_INTERVAL_MS : Math.min(Math.round(elapsed * 2.5), 4000);
                            scheduleNextSync(nextDelay);
                        }
                    }

                    let syncTimer = null;
                    function scheduleNextSync(delayMs) {
                        if (syncTimer) clearTimeout(syncTimer);
                        syncTimer = setTimeout(checkLiveSyncVersion, delayMs);
                    }

                    scheduleNextSync(LIVE_SYNC_INTERVAL_MS);
                    document.addEventListener('visibilitychange', function() {
                        if (document.visibilityState === 'visible') {
                            checkLiveSyncVersion();
                        }
                    });
                })();
                @endauth
            } catch (e) {}
        });
    </script>

    @auth
        <!-- Logout Confirmation Modal (GovTech AlertDialog) -->
        <div 
            id="logoutConfirmModal" 
            tabindex="-1"
            role="dialog"
            aria-labelledby="logoutModalTitle"
            aria-describedby="logoutModalDesc"
            aria-modal="true"
            onclick="if(event.target === this) closeLogoutModal()" 
            class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto">
                <!-- Modal Header -->
                <div class="shrink-0 p-5 sm:p-6 pb-0 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-sign-out text-2xl"></i>
                        </div>
                        <div>
                            <h3 id="logoutModalTitle" class="text-base sm:text-lg font-bold text-slate-900">
                                Confirm Sign Out
                            </h3>
                            <p class="text-xs text-slate-500 font-mono">
                                {{ Auth::user()->employee->first_name ?? Auth::user()->username }} {{ Auth::user()->employee->last_name ?? '' }} ({{ strtoupper(Auth::user()->role) }})
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        onclick="closeLogoutModal()" 
                        class="text-slate-400 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400"
                        aria-label="Close dialog"
                    >
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto flex-1 min-h-0 p-5 sm:p-6 py-4 space-y-3">
                    <p id="logoutModalDesc" class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Are you sure you want to sign out?
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="shrink-0 p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3">
                    <button 
                        type="button" 
                        id="btnCancelLogout"
                        onclick="closeLogoutModal()" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold transition cursor-pointer min-h-[40px] flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                    >
                        <i class="ph ph-arrow-u-up-left text-base"></i>
                        <span>Stay Signed In</span>
                    </button>
                    <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto m-0 p-0">
                        @csrf
                        <button 
                            type="submit" 
                            id="btnConfirmLogout"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer min-h-[40px] flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        >
                            <i class="ph-bold ph-sign-out text-base" id="logoutBtnIcon"></i>
                            <span id="logoutBtnText">Yes, Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Change Password (Self-Service for all authenticated users) -->
        <div 
            id="changePasswordModal" 
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="changePasswordModalTitle"
            class="{{ session('password_modal_open') ? '' : 'hidden' }} fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            onclick="if(event.target === this) closeChangePasswordModal()"
        >
            <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto" onclick="event.stopPropagation()">
                <!-- Header -->
                <div class="shrink-0 bg-gov-navy text-white px-5 sm:px-6 py-4 flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-400/40 text-amber-300 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-key text-xl"></i>
                        </div>
                        <div>
                            <h3 id="changePasswordModalTitle" class="text-base font-bold text-white leading-tight">Change Password</h3>
                            <p class="text-xs text-slate-300 font-mono">{{ Auth::user()->employee->employee_no ?? Auth::user()->email ?? 'Civil Service Account' }}</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        onclick="closeChangePasswordModal()" 
                        class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-400"
                        aria-label="Close dialog"
                    >
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.change') }}" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf

                    <div class="overflow-y-auto flex-1 min-h-0 p-5 sm:p-6 space-y-4">
                        <div>
                            <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Current Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your current password"
                                    class="w-full border @error('current_password') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                <button 
                                    type="button" 
                                    onclick="toggleInputType('current_password', this)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none"
                                    title="Show/hide password"
                                >
                                    <i class="ph ph-eye text-base"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    placeholder="Minimum 6 characters"
                                    class="w-full border @error('new_password') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                <button 
                                    type="button" 
                                    onclick="toggleInputType('new_password', this)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none"
                                    title="Show/hide password"
                                >
                                    <i class="ph ph-eye text-base"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password_confirmation" 
                                    name="new_password_confirmation" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    placeholder="Re-enter new password"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                <button 
                                    type="button" 
                                    onclick="toggleInputType('new_password_confirmation', this)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none"
                                    title="Show/hide password"
                                >
                                    <i class="ph ph-eye text-base"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-5 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3">
                        <button 
                            type="button" 
                            onclick="closeChangePasswordModal()" 
                            class="w-full sm:w-auto px-4 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[40px] flex items-center justify-center"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-5 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer min-h-[40px] flex items-center justify-center gap-1.5"
                        >
                            <i class="ph-bold ph-check text-base"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Full-Screen Logout Loading Overlay Modal (Circular Style) -->
        <div 
            id="logoutOverlayModal" 
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="logoutOverlayTitle"
            class="hidden fixed inset-0 z-[100] bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300"
        >
            <div class="bg-gov-navy border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 ring-1 ring-rose-400/20 transform animate-in fade-in zoom-in-95 duration-200">
                <!-- Circular SVG Spinner -->
                <div class="relative inline-flex items-center justify-center">
                    <svg class="animate-spin w-20 h-20 text-rose-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="50" cy="50" r="42" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="6" stroke-dasharray="264" stroke-dashoffset="198" stroke-linecap="round"/>
                    </svg>
                </div>

                <!-- Modal Status Text -->
                <div class="space-y-1.5 pt-1">
                    <h3 id="logoutOverlayTitle" class="text-base sm:text-lg font-bold text-white tracking-wide">
                        Signing Out...
                    </h3>
                </div>
            </div>
        </div>
    @endauth

    <!-- Full-Screen Refresh Loading Overlay Modal (Circular Style with Animation) -->
    <div 
        id="refreshOverlayModal" 
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="refreshOverlayTitle"
        class="hidden fixed inset-0 z-[100] bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300"
    >
        <div class="bg-gov-navy border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 ring-1 ring-amber-400/30 transform animate-in fade-in zoom-in-95 duration-200">
            <!-- Circular SVG Spinner -->
            <div class="relative inline-flex items-center justify-center">
                <svg class="animate-spin w-20 h-20 text-amber-400" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="50" cy="50" r="42" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="6" stroke-dasharray="264" stroke-dashoffset="198" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Modal Status Text -->
            <div class="space-y-1.5 pt-1">
                <h3 id="refreshOverlayTitle" class="text-base sm:text-lg font-bold text-white tracking-wide">
                    Refreshing Page...
                </h3>
            </div>
        </div>
    </div>

    <!-- Centered Refresh Completed Floating Dialog Modal -->
    <div 
        id="refreshCompletedModal" 
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="refreshCompletedTitle"
        class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto transition-all duration-300"
        onclick="if(event.target === this) closeRefreshCompletedModal()"
    >
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 my-auto" onclick="event.stopPropagation()">
            <!-- Top Header -->
            <div class="shrink-0 bg-slate-50 px-5 sm:px-6 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                <button 
                    type="button" 
                    onclick="closeRefreshCompletedModal()" 
                    class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    aria-label="Close dialog"
                >
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1 min-h-0 p-5 sm:p-7 space-y-4">
                <div class="text-center space-y-3">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 border-4 border-emerald-200/80 shadow-md">
                        <i class="ph-bold ph-check text-3xl"></i>
                    </div>
                    <div>
                        <h3 id="refreshCompletedTitle" class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                            Refresh Complete
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="shrink-0 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                <button 
                    type="button" 
                    onclick="closeRefreshCompletedModal()" 
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white text-xs sm:text-sm font-bold shadow-md transition cursor-pointer flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[40px]"
                >
                    <span>Close</span>
                </button>
            </div>
        </div>
    </div>

    @yield('scripts')
</body>
</html>



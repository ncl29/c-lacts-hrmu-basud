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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap');
        
        /* Baseline font scale */
        html {
            font-size: 16px;
            transition: font-size 0.12s ease-in-out;
        }
        html.text-size-standard,
        html.text-size-normal {
            font-size: 16px;
        }
        html.text-size-large {
            font-size: 18.5px;
        }
        html.text-size-xl {
            font-size: 21px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
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

                const isHighContrast = localStorage.getItem('clacts_high_contrast') === 'true' || localStorage.getItem('clacts_senior_mode') === 'true';
                if (isHighContrast) {
                    document.documentElement.classList.add('high-contrast-mode');
                }

                const isSimplified = localStorage.getItem('clacts_simplified_layout') === 'true';
                if (isSimplified) {
                    document.documentElement.classList.add('simplified-layout-mode');
                }

                if (localStorage.getItem('clacts_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col text-slate-800 antialiased selection:bg-amber-500 selection:text-white bg-slate-100/60">

    <!-- Top GovTech Official Header (Sticky) -->
    <header class="sticky top-0 z-40 bg-gov-navy text-white border-b border-slate-800/90 shadow-md">
        <!-- Republic Micro-Tricolor Line -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-700 via-amber-400 to-rose-600"></div>

        <div class="w-full max-w-7xl 2xl:max-w-[1600px] mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 py-2.5 sm:py-3">
            <div class="flex items-center justify-between gap-2">
                <!-- Branding with Official Municipal Seal -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    @auth
                        <!-- Mobile navigation side panel toggle -->
                        <button type="button" onclick="toggleSidePanel()" class="md:hidden p-1.5 sm:p-2 min-h-[38px] min-w-[38px] sm:min-h-[40px] sm:min-w-[40px] flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer shrink-0" aria-label="Toggle navigation side panel">
                            <i class="ph ph-list text-xl"></i>
                        </button>
                        <!-- Desktop collapsible sidebar button to maximize table workspace -->
                        <button type="button" onclick="toggleDesktopSidebar()" id="btnDesktopSidebarToggle" class="hidden md:flex p-2 min-h-[40px] min-w-[40px] items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent hover:border-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer shrink-0 transition" aria-label="Toggle sidebar width" title="Collapse sidebar to maximize table workspace">
                            <i class="ph ph-sidebar-simple text-xl" id="desktopSidebarIcon"></i>
                        </button>
                    @endauth

                    <a href="{{ Auth::check() && Auth::user()->role === 'hrmu_staff' ? route('hr.leave-records.index') : (Auth::check() && Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::check() && Auth::user()->role === 'routing_clerk' ? route('clerk.scanner') : route('employee.dashboard'))) }}" class="flex items-center gap-2 sm:gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 rounded-lg min-w-0">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white p-0.5 sm:p-1 ring-2 ring-amber-400/40 border border-amber-500/50 shadow-sm shrink-0 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/basud-logo.png') }}" alt="Seal of the Municipality of Basud" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <div class="text-[9px] min-[400px]:text-[10px] sm:text-[11px] uppercase tracking-wider text-amber-300/90 font-bold leading-tight font-mono truncate hidden min-[360px]:block">Republic of the Philippines • Region V</div>
                            <div class="text-sm min-[400px]:text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-1.5 sm:gap-2 truncate">
                                <span class="truncate">Basud</span>
                                <span class="hidden min-[360px]:inline">Municipality</span>
                                <span class="text-amber-300 text-[9px] sm:text-[10px] font-mono font-bold px-1.5 sm:px-2 py-0.5 rounded bg-amber-400/10 border border-amber-400/30 shrink-0">HRMU</span>
                            </div>
                            <div class="text-xs text-slate-300 hidden sm:block font-medium truncate">Centralized Leave Application & Credit Tracking System (C-LACTS)</div>
                        </div>
                    </a>
                </div>

                <!-- Header Right Controls: Employee Info & Accessibility Settings -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- User Profile Badge (Name & Position) -->
                    @auth
                        <div class="flex items-center gap-2 sm:gap-3 pr-1.5 sm:pr-3 border-r border-slate-700/80">
                            <a href="{{ route('profile.show') }}" class="text-right hidden sm:block group hover:opacity-90 transition rounded-lg p-1 -m-1 focus:outline-none focus:ring-1 focus:ring-amber-400" title="View & Edit My Profile">
                                <div class="text-sm font-bold text-white flex items-center justify-end gap-2 group-hover:text-amber-300 transition">
                                    <span>{{ Auth::user()->employee->first_name ?? Auth::user()->username }} {{ Auth::user()->employee->last_name ?? '' }}</span>
                                    @if(Auth::user()->role === 'admin')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/15 text-amber-300 border border-amber-400/40">ADMIN</span>
                                    @elseif(Auth::user()->role === 'hrmu_staff')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-sky-500/15 text-sky-300 border border-sky-400/40">HR STAFF</span>
                                    @elseif(Auth::user()->role === 'routing_clerk')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-400/40">CLERK</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">EMPLOYEE</span>
                                    @endif
                                    <i class="ph ph-gear text-xs text-slate-400 group-hover:text-amber-300 transition"></i>
                                </div>
                                <div class="text-xs text-slate-300 font-medium whitespace-normal leading-snug">{{ Auth::user()->employee->office->office_name ?? 'Basud Municipal Office' }}</div>
                            </a>
                        </div>
                    @else
                        <div class="pr-2 sm:pr-3 border-r border-slate-700">
                            <a href="{{ route('login') }}" class="px-3 sm:px-4 py-2 rounded-lg text-xs font-bold bg-gov-700 hover:bg-gov-800 text-white transition flex items-center gap-1.5 sm:gap-2 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 min-h-[38px]">
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
                            class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-900/90 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-700/80 shadow-xs text-xs font-semibold transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[38px] sm:min-h-[40px]" 
                            title="Accessibility and Display Settings"
                        >
                            <!-- Lucide Accessibility Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400 shrink-0" aria-hidden="true">
                                <circle cx="16" cy="4" r="1"/>
                                <path d="m18 19 1-7-6 1"/>
                                <path d="m5 8 3-3 5.5 3-2.36 3.5"/>
                                <path d="M4.24 14.5a5 5 0 0 0 6.88 6"/>
                                <path d="M13.76 17.5a5 5 0 0 0-6.88-6"/>
                            </svg>
                            <span class="hidden sm:inline font-sans font-medium text-xs">Accessibility</span>
                            <i class="ph ph-caret-down text-xs text-slate-400 transition-transform duration-150" id="accessibilityCaretIcon"></i>
                        </button>

                        <!-- Dropdown Menu Panel -->
                        <div 
                            id="accessibilityDropdownPanel" 
                            role="dialog"
                            aria-label="Accessibility & Display Settings"
                            class="hidden absolute right-0 top-full mt-2 w-72 sm:w-80 bg-white text-slate-900 rounded-2xl shadow-2xl border border-slate-200 z-50 p-4 space-y-4 focus:outline-none"
                        >
                            <!-- Panel Header -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <i class="ph-bold ph-sliders text-amber-600 text-base"></i>
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider font-mono">Accessibility Settings</h3>
                                </div>
                                <button 
                                    type="button" 
                                    onclick="closeAccessibilityDropdown()" 
                                    class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                                    aria-label="Close accessibility settings"
                                >
                                    <i class="ph ph-x text-sm"></i>
                                </button>
                            </div>

                            <!-- 1. Text Size Segmented Selector -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="ph ph-text-aa text-sm text-amber-600"></i>
                                        <span>Text Size</span>
                                    </label>
                                    <span id="currentTextSizeLabel" class="text-[11px] font-mono font-semibold text-slate-500">Standard</span>
                                </div>
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

                            <!-- 2. High Contrast Option -->
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="pr-2">
                                    <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <i class="ph-bold ph-circle-half text-sm text-amber-600"></i>
                                        <span>High Contrast</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight mt-0.5">Deep black text, reinforced borders & sharp focus</div>
                                </div>
                                <button 
                                    type="button" 
                                    role="switch" 
                                    aria-checked="false" 
                                    id="toggleHighContrastBtn" 
                                    onclick="toggleHighContrastSetting()" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                >
                                    <span class="sr-only">Toggle high contrast</span>
                                    <span id="toggleHighContrastIndicator" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                </button>
                            </div>

                            <!-- 3. Simplified Layout Option -->
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="pr-2">
                                    <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <i class="ph-bold ph-layout text-sm text-amber-600"></i>
                                        <span>Simplified Layout</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight mt-0.5">Spacious table rows, flat borders & reduced clutter</div>
                                </div>
                                <button 
                                    type="button" 
                                    role="switch" 
                                    aria-checked="false" 
                                    id="toggleSimplifiedLayoutBtn" 
                                    onclick="toggleSimplifiedLayoutSetting()" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                >
                                    <span class="sr-only">Toggle simplified layout</span>
                                    <span id="toggleSimplifiedLayoutIndicator" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                </button>
                            </div>

                            <!-- 4. Reset Display Settings -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                <button 
                                    type="button" 
                                    onclick="resetAccessibilitySettings()" 
                                    class="w-full py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition cursor-pointer flex items-center justify-center gap-2"
                                >
                                    <i class="ph ph-arrow-counter-clockwise text-sm"></i>
                                    <span>Reset Display Settings</span>
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
            <aside id="sidePanel" class="fixed top-0 md:top-[65px] bottom-0 left-0 z-50 md:z-30 w-72 bg-slate-900 text-slate-200 border-r border-slate-800 shrink-0 flex flex-col justify-between transition-transform duration-200 ease-in-out -translate-x-full md:translate-x-0 h-full md:h-[calc(100vh-65px)] overflow-hidden">
                
                <!-- Mobile Close Bar -->
                <div class="md:hidden flex items-center justify-between p-3 border-b border-slate-800 bg-slate-950 shrink-0">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest font-mono">Navigation Menu</span>
                    <button type="button" onclick="toggleSidePanel()" class="text-slate-300 hover:text-white p-1 rounded-lg cursor-pointer">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Items -->
                <nav class="p-3 pt-4 sm:pt-5 space-y-1 sm:space-y-1.5 flex-1 overflow-hidden flex flex-col justify-start">
                    <!-- Section Label: Employee Portal (Hidden for pure Routing Clerks and HRMU Staff) -->
                    @if(!in_array(Auth::user()->role, ['hrmu_staff', 'routing_clerk']))
                        <div class="px-2.5 pt-1 pb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Employee Services</span>
                        </div>

                        <a href="{{ route('employee.dashboard') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('employee.dashboard') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('employee.dashboard') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-squares-four text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Self-Service</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Dashboard & Leave Filing</div>
                            </div>
                        </a>

                        <a href="{{ route('employee.leave-record') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('employee.leave-record*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('employee.leave-record*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-notebook text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">My Leave Record</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Ledger Card & Balance</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Physical Workflow (QR Scanner for Clerk, HR Staff, Admin, Dept Head, and Mayor) -->
                    @if(Auth::user()->canAccessQrScanner())
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Document Routing</span>
                        </div>

                        <a href="{{ route('clerk.scanner') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('clerk.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}" title="Unified QR barcode scanning and document custody terminal">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('clerk.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-qr-code text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">QR Document Scanner</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Receipt & Dispatch Desk</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: HR Management -->
                    @if(in_array(Auth::user()->role, ['hrmu_staff', 'admin']))
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">HR Administration</span>
                        </div>

                        <a href="{{ route('hr.leave-records.index') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.leave-records.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.leave-records.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-identification-badge text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Leave Records</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Personnel Balances & Ledgers</div>
                            </div>
                        </a>

                        <a href="{{ route('hr.tardiness.create') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.tardiness.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.tardiness.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-calculator text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">HR Starla Engine</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Deduction & Accrual Calculator</div>
                            </div>
                        </a>

                        <a href="{{ route('hr.starla.tables') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('hr.starla.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('hr.starla.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-table text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Starla Tables</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">CSC Tables II, III & IV Matrix</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Administration -->
                    @if(Auth::user()->role === 'admin')
                        <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">System Oversight</span>
                        </div>

                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('admin.*') && request()->query('tab') !== 'backups' ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.*') && request()->query('tab') !== 'backups' ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-shield-star text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Admin Control</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Audit Logs & User Accounts</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.dashboard', ['tab' => 'backups']) }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->query('tab') === 'backups' ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->query('tab') === 'backups' ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                <i class="ph-bold ph-archive text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1 leading-tight">
                                <div class="truncate">Backups & Recovery</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Database & Document Archives</div>
                            </div>
                        </a>
                    @endif

                    <!-- Section Label: Account & Settings (Placed last, accessible to all authenticated personnel) -->
                    <div class="px-2.5 pt-1.5 pb-0.5 border-t border-slate-800/70">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Account & Settings</span>
                    </div>

                    <a href="{{ route('profile.show') }}" class="px-3 py-2 rounded-xl flex items-center gap-2.5 text-xs sm:text-sm transition {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white font-bold border-l-4 border-amber-400 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-semibold' }}" title="View and edit personal details and change password">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('profile.*') ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                            <i class="ph-bold ph-user-gear text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1 leading-tight">
                            <div class="truncate">My Profile & Settings</div>
                            <div class="text-[10px] font-mono text-slate-400 font-normal truncate">Personnel Info & Password</div>
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
                    class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 animate-in fade-in"
                    onclick="if(event.target === this) closeCancellationModal()"
                >
                    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border-t-4 border-t-rose-500 border-x border-b border-slate-200 overflow-hidden transform animate-in zoom-in-95 duration-200 my-auto">
                        <!-- Top Header with Basud Municipal Branding -->
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/90 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider font-mono">Civil Service Form Cancellation</span>
                            </div>
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
                        <div class="p-6 sm:p-8 space-y-6">
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
                                            <span><strong>Ledger Credits Restored:</strong> Previously deducted leave credits have been reverted and re-credited to your certified balance ledger.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end gap-3">
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
                    class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300"
                    onclick="if(event.target === this) closeLoginSuccessModal()"
                >
                    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200">
                        <!-- Top Header with Basud Municipal Branding -->
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/90 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            </div>
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
                        <div class="p-6 sm:p-8 space-y-6">
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

                            <!-- Session Receipt Summary Card -->
                            @auth
                                <div class="rounded-2xl bg-slate-50 border border-slate-200/90 p-4 space-y-3 text-xs">
                                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-200 text-slate-500 font-mono text-[11px]">
                                        <span class="uppercase tracking-wider">Session Receipt</span>
                                        <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">VERIFIED ACTIVE</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                        <div>
                                            <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Personnel</div>
                                            <div class="text-xs font-bold text-slate-900 mt-0.5">
                                                {{ Auth::user()->employee->first_name ?? Auth::user()->username }} {{ Auth::user()->employee->last_name ?? '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Station / Office</div>
                                            <div class="text-xs font-bold text-slate-900 mt-0.5 leading-snug">
                                                {{ Auth::user()->employee->office->office_name ?? 'Basud Municipal Office' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Authorized Role</div>
                                            <div class="text-xs font-bold text-slate-900 mt-0.5 uppercase font-mono">
                                                {{ str_replace('_', ' ', Auth::user()->role) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] uppercase font-mono text-slate-400 font-bold">Login Timestamp</div>
                                            <div class="text-xs font-bold text-slate-900 mt-0.5 font-mono">
                                                {{ now()->format('M d, Y • h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endauth
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end">
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

            <!-- Standard In-Page Operational Alerts (Success, Error, Warning, Info) -->
            <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 mt-3 sm:mt-4 w-full">

                @if(session('success') && !session('login_success') && !session('recalculate_success') && !session('cancellation_success') && !str_contains(session('success'), 'Welcome') && !str_contains(session('success'), 'cancelled'))
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-xs flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-check-circle text-emerald-600 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-900">Success</h4>
                                <p class="text-xs text-emerald-800 mt-0.5">{{ session('success') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer" aria-label="Close notice">
                            <i class="ph ph-x text-base"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-xs flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-warning-circle text-rose-600 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-rose-900">Please correct the following errors:</h4>
                                <ul class="list-disc list-inside text-xs text-rose-800 mt-1 space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 cursor-pointer" aria-label="Close notice">
                            <i class="ph ph-x text-base"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-xs flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-warning-circle text-rose-600 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-rose-900">Operational Notice</h4>
                                <p class="text-xs text-rose-800 mt-0.5">{{ session('error') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 cursor-pointer" aria-label="Close notice">
                            <i class="ph ph-x text-base"></i>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-xs flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-warning text-amber-600 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-amber-900">Notice</h4>
                                <p class="text-xs text-amber-800 mt-0.5">{{ session('warning') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-800 p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 cursor-pointer" aria-label="Close notice">
                            <i class="ph ph-x text-base"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-sky-50 border-l-4 border-gov-600 p-4 rounded-r-xl shadow-xs flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-info text-gov-700 text-xl shrink-0 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-gov-900">Information</h4>
                                <p class="text-xs text-gov-800 mt-0.5">{{ session('info') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-gov-700 hover:text-gov-900 p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-gov-600 cursor-pointer" aria-label="Close information">
                            <i class="ph ph-x text-base"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6">
                @yield('content')
            </main>

            <!-- GovTech System Footer -->
            <footer class="bg-white border-t border-slate-200 mt-auto py-3.5 sm:py-4 text-xs text-slate-500">
                <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-2.5 sm:px-4 md:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                    <div class="flex items-center gap-2">
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
                indicator.classList.add('translate-x-5');
                indicator.classList.remove('translate-x-0');
            } else {
                btn.classList.remove('bg-amber-500');
                btn.classList.add('bg-slate-300');
                indicator.classList.remove('translate-x-5');
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
                indicator.classList.add('translate-x-5');
                indicator.classList.remove('translate-x-0');
            } else {
                btn.classList.remove('bg-amber-500');
                btn.classList.add('bg-slate-300');
                indicator.classList.remove('translate-x-5');
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
                        closeCancellationModal();
                        closeRefreshCompletedModal();
                    }
                });

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
            class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto">
                <!-- Modal Header -->
                <div class="p-6 pb-0 flex items-start justify-between gap-4">
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
                <div class="p-6 py-4 space-y-3">
                    <p id="logoutModalDesc" class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Are you sure you want to end your current session in <strong>C-LACTS</strong>? Any unsaved edits or pending entries will be closed.
                    </p>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-center gap-2">
                        <i class="ph ph-buildings text-base text-slate-500 shrink-0"></i>
                        <span>Station: <strong>{{ Auth::user()->employee->office->office_name ?? 'Basud Municipal Office' }}</strong></span>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="p-6 pt-0 bg-slate-50/50 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-slate-100">
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
            class="{{ session('password_modal_open') || (isset($errors) && ($errors->has('current_password') || $errors->has('new_password'))) ? '' : 'hidden' }} fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            onclick="if(event.target === this) closeChangePasswordModal()"
        >
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto" onclick="event.stopPropagation()">
                <!-- Header -->
                <div class="bg-gov-navy text-white px-5 sm:px-6 py-4 flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-400/40 text-amber-300 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-key text-xl"></i>
                        </div>
                        <div>
                            <h3 id="changePasswordModalTitle" class="text-base font-bold text-white leading-tight">Change Password</h3>
                            <p class="text-xs text-slate-300 font-mono">@<span>{{ Auth::user()->username }}</span> • Civil Service Account</p>
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
                <form method="POST" action="{{ route('password.change') }}" class="p-5 sm:p-6 space-y-4">
                    @csrf

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

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            onclick="closeChangePasswordModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[38px]"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer min-h-[38px] flex items-center gap-1.5"
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
            <div class="bg-gov-navy border border-slate-700/80 rounded-3xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 ring-1 ring-rose-400/20 transform animate-in fade-in zoom-in-95 duration-200">
                <!-- Circular SVG Spinner with Center Municipal Seal -->
                <div class="relative inline-flex items-center justify-center">
                    <svg class="animate-spin w-24 h-24 text-rose-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="50" cy="50" r="42" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="6" stroke-dasharray="264" stroke-dashoffset="198" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute w-14 h-14 bg-slate-900 rounded-full p-2 flex items-center justify-center border border-slate-700/80 shadow-md">
                        <img src="{{ asset('images/basud-logo.png') }}" alt="Basud LGU Seal" class="w-full h-full object-contain">
                    </div>
                </div>

                <!-- Modal Status Text -->
                <div class="space-y-1.5 pt-1">
                    <h3 id="logoutOverlayTitle" class="text-base sm:text-lg font-bold text-white tracking-wide">
                        Signing Out...
                    </h3>
                    <p class="text-xs text-slate-300 font-mono">
                        Closing terminal session & saving audit log...
                    </p>
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
        <div class="bg-gov-navy border border-slate-700/80 rounded-3xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 ring-1 ring-amber-400/30 transform animate-in fade-in zoom-in-95 duration-200">
            <!-- Circular SVG Spinner with Center Municipal Seal -->
            <div class="relative inline-flex items-center justify-center">
                <svg class="animate-spin w-24 h-24 text-amber-400" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="50" cy="50" r="42" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="6" stroke-dasharray="264" stroke-dashoffset="198" stroke-linecap="round"/>
                </svg>
                <div class="absolute w-14 h-14 bg-slate-900 rounded-full p-2 flex items-center justify-center border border-slate-700/80 shadow-md">
                    <img src="{{ asset('images/basud-logo.png') }}" alt="Basud LGU Seal" class="w-full h-full object-contain">
                </div>
            </div>

            <!-- Modal Status Text -->
            <div class="space-y-1.5 pt-1">
                <h3 id="refreshOverlayTitle" class="text-base sm:text-lg font-bold text-white tracking-wide">
                    Refreshing Page...
                </h3>
                <p class="text-xs text-slate-300 font-mono">
                    Reloading records and municipal ledger data...
                </p>
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
        class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300"
        onclick="if(event.target === this) closeRefreshCompletedModal()"
    >
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
            <!-- Top Header with Basud Municipal Branding -->
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/90 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-700 font-mono tracking-wider uppercase">Page Refreshed</span>
                </div>
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
            <div class="p-6 sm:p-7 space-y-4">
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
            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-end">
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



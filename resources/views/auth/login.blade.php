<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Civil Service Login — C-LACTS HRMU Basud</title>

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
                            600: '#1b4d8f',
                            700: '#143c72',
                            800: '#10305c',
                            900: '#0a1e3b',
                            navy: '#081426',
                            gold: '#c2943b',
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

        /* High Contrast & Senior Legibility Mode */
        html.high-contrast-mode body,
        html.senior-vision-mode body {
            background-color: #020617 !important;
        }
        html.high-contrast-mode .bg-white,
        html.senior-vision-mode .bg-white {
            background-color: #ffffff !important;
            border-width: 2px !important;
            border-color: #cbd5e1 !important;
        }
        html.high-contrast-mode input,
        html.senior-vision-mode input {
            color: #000000 !important;
            font-weight: 700 !important;
            border: 2.5px solid #334155 !important;
            background-color: #ffffff !important;
        }
        html.high-contrast-mode label,
        html.senior-vision-mode label {
            color: #000000 !important;
            font-weight: 800 !important;
        }
        html.high-contrast-mode button:not([role="switch"]),
        html.senior-vision-mode button:not([role="switch"]) {
            font-weight: 800 !important;
            border-width: 1.5px !important;
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
        html.simplified-layout-mode input {
            box-shadow: none !important;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 0.5rem !important;
        }

        /* Prevent browser/Edge clear button and typing indicators */
        input::-ms-clear,
        input::-ms-reveal {
            display: none;
            width: 0;
            height: 0;
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
            } catch (e) {}
        })();
    </script>
</head>
<body class="min-h-full flex flex-col justify-center py-10 sm:px-6 lg:px-8 bg-gov-navy text-slate-100 antialiased selection:bg-amber-500 selection:text-white relative overflow-x-hidden">

    <!-- Top Republic Micro-Tricolor Line -->
    <div class="fixed top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-700 via-amber-400 to-rose-600 z-50"></div>

    <!-- Top Accessibility Settings Dropdown -->
    <div class="fixed top-4 right-4 z-50" id="accessibilityDropdownContainer">
        <div class="relative">
            <button 
                type="button" 
                id="btnAccessibilityToggle"
                onclick="toggleAccessibilityDropdown()" 
                class="px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-xl border border-slate-700 bg-slate-900/90 text-slate-200 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5 sm:gap-2 shadow-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-400"
                aria-haspopup="dialog"
                aria-expanded="false"
                title="Accessibility & Display Settings"
            >
                <i class="ph-bold ph-sliders-horizontal text-base sm:text-lg text-amber-400 shrink-0" aria-hidden="true"></i>
                <span class="font-sans font-medium text-xs">Accessibility</span>
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

    <!-- Header Section with Official Republic Branding -->
    <div class="sm:mx-auto sm:w-full sm:max-w-xl text-center px-4">
        <!-- Municipal Official Seal Medallion with Antique Gold Ring -->
        <div class="inline-flex items-center justify-center w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-white p-1.5 ring-4 ring-amber-400/30 border-2 border-amber-500/50 shadow-2xl mb-4 transition transform hover:scale-105 duration-200">
            <img src="{{ asset('images/basud-logo.png') }}" alt="Seal of the Municipality of Basud" class="w-full h-full object-contain drop-shadow-sm">
        </div>

        <div class="text-[10px] sm:text-xs uppercase tracking-widest text-amber-300 font-bold font-mono">Republic of the Philippines • Province of Camarines Norte</div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white mt-1.5 drop-shadow-sm">Municipality of Basud</h1>
        <p class="text-xs sm:text-sm text-slate-300 font-semibold tracking-wide mt-1">
            Human Resource Management Unit (HRMU)
        </p>
        <div class="mt-3.5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-mono font-bold bg-amber-400/10 border border-amber-400/30 text-amber-300 shadow-xs">
            <i class="ph-bold ph-shield-check text-sm text-amber-400"></i>
            <span>C-LACTS Official Administration Terminal</span>
        </div>
    </div>

    <!-- Login Card Container -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg lg:max-w-xl px-3 sm:px-0">
        <div class="bg-white text-slate-800 rounded-2xl shadow-2xl border-t-4 border-t-amber-400 border-x border-b border-slate-200/90 overflow-hidden">

            <!-- Form Body -->
            <div class="p-6 sm:p-10 space-y-5 sm:space-y-6">

                <!-- Flash Status Alerts -->
                @if(session('info'))
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-900 rounded-r-2xl text-xs sm:text-sm font-bold flex items-center gap-3 shadow-xs">
                        <i class="ph-fill ph-info text-blue-600 text-xl shrink-0"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-950 rounded-r-2xl text-xs sm:text-sm font-bold flex items-center gap-3 shadow-xs">
                        <i class="ph-fill ph-warning text-amber-600 text-xl shrink-0"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-950 rounded-r-2xl text-xs sm:text-sm font-bold flex items-center gap-3 shadow-xs">
                        <i class="ph-fill ph-warning-circle text-rose-600 text-xl shrink-0"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl text-xs sm:text-sm text-rose-900 space-y-2 font-bold shadow-xs">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2.5">
                                <i class="ph-bold ph-x-circle text-rose-600 text-lg shrink-0"></i>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                    <!-- Client-side Reset Success Banner -->
                    <div id="forgotSuccessBanner" class="hidden p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 rounded-r-2xl text-xs sm:text-sm font-medium flex items-center gap-3 shadow-xs">
                        <i class="ph-fill ph-check-circle text-emerald-600 text-xl shrink-0"></i>
                        <span id="forgotSuccessText">Password updated. Sign in with your new password.</span>
                    </div>

                    <!-- Authentication Form -->
                <form id="loginForm" method="POST" action="{{ route('login.submit') }}" class="space-y-5" autocomplete="off">
                    @csrf

                    <!-- Employee ID or Email -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 font-mono">
                            Employee ID / Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="ph-bold ph-identification-badge text-xl"></i>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="{{ old('username') }}" 
                                required 
                                autofocus 
                                autocomplete="off"
                                spellcheck="false"
                                autocorrect="off"
                                autocapitalize="off"
                                data-ms-editor="false"
                                placeholder="Enter Employee ID or Email"
                                class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-500 bg-slate-50/50 hover:bg-white focus:bg-white transition"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 font-mono">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="ph-bold ph-lock-key text-xl"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter password"
                                class="w-full pl-11 pr-12 py-3 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-500 bg-slate-50/50 hover:bg-white focus:bg-white transition"
                            >
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none rounded cursor-pointer"
                                title="Toggle password visibility"
                            >
                                <i class="ph ph-eye text-xl" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember"
                                value="1" 
                                {{ old('remember') ? 'checked' : '' }}
                                class="rounded border-slate-300 text-gov-700 focus:ring-amber-400 w-4 h-4 cursor-pointer"
                            >
                            <span class="text-xs text-slate-700 font-semibold">Remember Me</span>
                        </label>
                        <button
                            type="button"
                            onclick="openForgotPasswordModal()"
                            class="text-xs font-medium text-gov-700 hover:text-gov-800 hover:underline cursor-pointer"
                        >
                            Forgot Password?
                        </button>
                    </div>

                    <!-- Submit Button with Key/Shield Login Icon -->
                    <button 
                        type="submit" 
                        id="btnLogin"
                        class="w-full mt-2 py-3.5 bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white rounded-xl text-sm sm:text-base font-bold shadow-md hover:shadow-lg transition flex items-center justify-center gap-2.5 cursor-pointer border border-gov-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 min-h-[48px]"
                    >
                        <i class="ph-bold ph-key text-xl text-amber-300" id="loginBtnIcon"></i>
                        <span id="loginBtnText" class="tracking-wide">LOGIN</span>
                    </button>
                </form>

            </div>

        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 mt-8 space-y-1.5 font-medium">
            <div class="flex items-center justify-center gap-2">
                <i class="ph ph-buildings text-sm text-slate-400"></i>
                <span>Human Resource Management Unit • LGU Municipality of Basud</span>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div
        id="forgotPasswordModal"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="forgotModalTitle"
        onclick="if(event.target === this) closeForgotPasswordModal()"
        class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div class="bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 id="forgotModalTitle" class="text-base font-medium text-slate-900">Reset Password</h3>
                <button
                    type="button"
                    onclick="closeForgotPasswordModal()"
                    class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    aria-label="Close dialog"
                >
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div id="forgotModalAlert" class="hidden p-3 rounded-xl text-xs font-medium flex items-center gap-2"></div>

                <!-- Step 1: Email -->
                <div class="space-y-2">
                    <label for="forgotEmailInput" class="block text-xs font-medium text-slate-700 uppercase tracking-wider font-mono">
                        Registered Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="forgotEmailInput"
                        placeholder="Enter your saved email"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-gov-500/20 focus:border-gov-600 bg-white"
                    >
                </div>

                <!-- Step 2: OTP & New Passcode -->
                <div id="forgotStep2" class="hidden space-y-4 pt-2 border-t border-slate-100">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="forgotOtpInput" class="block text-xs font-medium text-slate-700 uppercase tracking-wider font-mono">
                                6-Digit Code <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" id="btnResendForgotOtp" onclick="sendForgotOtp()" class="text-xs text-gov-700 hover:underline cursor-pointer">
                                Resend Code
                            </button>
                        </div>
                        <input
                            type="text"
                            id="forgotOtpInput"
                            maxlength="6"
                            inputmode="numeric"
                            placeholder="000000"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm font-mono tracking-widest text-slate-900 focus:ring-2 focus:ring-gov-500/20 focus:border-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="forgotNewPassInput" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            id="forgotNewPassInput"
                            placeholder="Minimum 6 characters"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-gov-500/20 focus:border-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="forgotConfirmPassInput" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            Confirm Password <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            id="forgotConfirmPassInput"
                            placeholder="Re-enter new password"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-gov-500/20 focus:border-gov-600 bg-white"
                        >
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-5 py-3.5 border-t border-slate-200 flex items-center justify-end gap-2">
                <button
                    type="button"
                    onclick="closeForgotPasswordModal()"
                    class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    id="btnSendForgotOtp"
                    onclick="sendForgotOtp()"
                    class="px-4 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs font-medium transition cursor-pointer"
                >
                    Send Code
                </button>
                <button
                    type="button"
                    id="btnResetForgotPw"
                    onclick="submitForgotReset()"
                    class="hidden px-4 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs font-medium transition cursor-pointer"
                >
                    Reset Password
                </button>
            </div>
        </div>
    </div>

    <!-- Full-Screen Overlay Modal Spinner (Formal GovTech Circular Style) -->
    <div 
        id="loginOverlayModal" 
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="loginOverlayTitle"
        aria-describedby="loginOverlayDesc"
        class="hidden fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300"
    >
        <div class="bg-gov-navy border border-slate-700/80 rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-5 ring-1 ring-amber-400/20 transform animate-in fade-in zoom-in-95 duration-200">
            <!-- Circular Spinner -->
            <div class="relative inline-flex items-center justify-center">
                <!-- Circular SVG Track & Animated Spinner Head -->
                <svg class="animate-spin w-20 h-20 text-amber-400" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- Background Circle Track -->
                    <circle cx="50" cy="50" r="42" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                    <!-- Animated Circular Arc Spinner -->
                    <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="6" stroke-dasharray="264" stroke-dashoffset="198" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Modal Text -->
            <div class="space-y-1.5 pt-1">
                <h3 id="loginOverlayTitle" class="text-base sm:text-lg font-bold text-white tracking-wide">
                    Authenticating
                </h3>
                <p id="loginOverlayDesc" class="text-xs text-slate-300 font-mono">
                    Verifying credentials...
                </p>
            </div>
        </div>
    </div>

    <script>
        function openForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            const alertBox = document.getElementById('forgotModalAlert');
            const step2 = document.getElementById('forgotStep2');
            const btnSend = document.getElementById('btnSendForgotOtp');
            const btnReset = document.getElementById('btnResetForgotPw');
            const emailInput = document.getElementById('forgotEmailInput');
            const loginField = document.getElementById('username');

            if (alertBox) alertBox.classList.add('hidden');
            if (step2) step2.classList.add('hidden');
            if (btnSend) {
                btnSend.classList.remove('hidden');
                btnSend.disabled = false;
                btnSend.innerText = 'Send Code';
            }
            if (btnReset) btnReset.classList.add('hidden');
            if (emailInput) {
                emailInput.readOnly = false;
                emailInput.value = (loginField && loginField.value.includes('@')) ? loginField.value.trim() : '';
            }
            document.getElementById('forgotOtpInput').value = '';
            document.getElementById('forgotNewPassInput').value = '';
            document.getElementById('forgotConfirmPassInput').value = '';

            if (modal) modal.classList.remove('hidden');
            if (emailInput) emailInput.focus();
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            if (modal) modal.classList.add('hidden');
        }

        function showForgotAlert(message, isError = true) {
            const box = document.getElementById('forgotModalAlert');
            if (!box) return;
            box.className = isError
                ? 'p-3 rounded-xl text-xs font-medium flex items-center gap-2 bg-rose-50 text-rose-800 border border-rose-200'
                : 'p-3 rounded-xl text-xs font-medium flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200';
            box.innerText = message;
            box.classList.remove('hidden');
        }

        async function sendForgotOtp() {
            const email = document.getElementById('forgotEmailInput').value.trim();
            const btnSend = document.getElementById('btnSendForgotOtp');
            const btnResend = document.getElementById('btnResendForgotOtp');
            if (!email) {
                showForgotAlert('Enter your registered email address.');
                return;
            }

            if (btnSend) { btnSend.disabled = true; btnSend.innerText = 'Sending...'; }
            if (btnResend) { btnResend.disabled = true; btnResend.innerText = 'Sending...'; }

            try {
                const res = await fetch("{{ route('password.forgot.send-otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    const firstErr = data.errors ? Object.values(data.errors)[0][0] : null;
                    showForgotAlert(firstErr || data.message || 'Failed to send verification code.');
                    if (btnSend) { btnSend.disabled = false; btnSend.innerText = 'Send Code'; }
                    if (btnResend) { btnResend.disabled = false; btnResend.innerText = 'Resend Code'; }
                    return;
                }

                showForgotAlert(data.message, false);
                document.getElementById('forgotEmailInput').readOnly = true;
                document.getElementById('forgotStep2').classList.remove('hidden');
                if (btnSend) btnSend.classList.add('hidden');
                document.getElementById('btnResetForgotPw').classList.remove('hidden');
                if (btnResend) { btnResend.disabled = false; btnResend.innerText = 'Resend Code'; }
                document.getElementById('forgotOtpInput').focus();
            } catch (e) {
                showForgotAlert('Connection error. Please try again.');
                if (btnSend) { btnSend.disabled = false; btnSend.innerText = 'Send Code'; }
                if (btnResend) { btnResend.disabled = false; btnResend.innerText = 'Resend Code'; }
            }
        }

        async function submitForgotReset() {
            const email = document.getElementById('forgotEmailInput').value.trim();
            const otp = document.getElementById('forgotOtpInput').value.trim();
            const new_password = document.getElementById('forgotNewPassInput').value;
            const new_password_confirmation = document.getElementById('forgotConfirmPassInput').value;
            const btnReset = document.getElementById('btnResetForgotPw');

            btnReset.disabled = true;
            btnReset.innerText = 'Saving...';

            try {
                const res = await fetch("{{ route('password.forgot.reset') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, otp, new_password, new_password_confirmation })
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    const firstErr = data.errors ? Object.values(data.errors)[0][0] : null;
                    showForgotAlert(firstErr || data.message || 'Could not reset password.');
                    btnReset.disabled = false;
                    btnReset.innerText = 'Reset Password';
                    return;
                }

                closeForgotPasswordModal();
                const banner = document.getElementById('forgotSuccessBanner');
                const bannerText = document.getElementById('forgotSuccessText');
                if (banner && bannerText) {
                    bannerText.innerText = data.message;
                    banner.classList.remove('hidden');
                }
                const loginField = document.getElementById('username');
                if (loginField && data.email) {
                    loginField.value = data.email;
                }
                const passField = document.getElementById('password');
                if (passField) passField.focus();
            } catch (e) {
                showForgotAlert('Connection error. Please try again.');
                btnReset.disabled = false;
                btnReset.innerText = 'Reset Password';
            }
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            }
        }

        // Form Submission with Full-Screen Overlay Modal Spinner
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const btnLogin = document.getElementById('btnLogin');
            const loginOverlayModal = document.getElementById('loginOverlayModal');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            if (loginForm && btnLogin) {
                loginForm.addEventListener('submit', function(e) {
                    const u = usernameInput ? usernameInput.value.trim() : '';
                    const p = passwordInput ? passwordInput.value.trim() : '';

                    // Trigger full-screen overlay modal spinner only if both fields are populated
                    if (u.length > 0 && p.length > 0) {
                        btnLogin.classList.add('pointer-events-none', 'opacity-80');
                        if (loginOverlayModal) {
                            loginOverlayModal.classList.remove('hidden');
                        }
                    }
                });
            }

            // Sync accessibility on load
            syncAccessibilityUI();

            // Close accessibility dropdown when clicking outside or pressing Escape
            document.addEventListener('click', function(e) {
                const container = document.getElementById('accessibilityDropdownContainer');
                if (container && !container.contains(e.target)) {
                    closeAccessibilityDropdown();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAccessibilityDropdown();
                    closeForgotPasswordModal();
                }
            });
        });

        // Accessibility Dropdown Open / Close
        function toggleAccessibilityDropdown() {
            const panel = document.getElementById('accessibilityDropdownPanel');
            const caret = document.getElementById('accessibilityCaretIcon');
            const btn = document.getElementById('btnAccessibilityToggle');
            if (!panel) return;

            const isHidden = panel.classList.toggle('hidden');
            if (btn) btn.setAttribute('aria-expanded', (!isHidden).toString());
            if (caret) {
                if (!isHidden) caret.classList.add('rotate-180');
                else caret.classList.remove('rotate-180');
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
    </script>
</body>
</html>

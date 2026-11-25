@extends('layouts.app')

@section('title', 'My Profile & Account Settings - HRMU Basud')

@section('content')
<div class="space-y-6">

    <!-- Floating Dialog Modal: Password Successfully Changed with Email Verification -->
    @if(session('password_update_success'))
        <div 
            id="passwordSuccessModal" 
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="passwordSuccessTitle"
            class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300"
            onclick="if(event.target === this) closePasswordSuccessModal()"
        >
            <div class="bg-white rounded-3xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 my-auto" onclick="event.stopPropagation()">
                <!-- Header with Close Button -->
                <div class="shrink-0 bg-slate-50 px-6 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                    <button 
                        type="button" 
                        onclick="closePasswordSuccessModal()" 
                        class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        aria-label="Close dialog"
                    >
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="overflow-y-auto flex-1 min-h-0 p-6 sm:p-7 space-y-4">
                    <div class="text-center space-y-3">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 border-4 border-emerald-200/80 shadow-md">
                            <i class="ph-bold ph-check text-3xl"></i>
                        </div>
                        <div>
                            <h3 id="passwordSuccessTitle" class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                Password Updated Successfully
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">
                                Your account credentials have been securely updated and authenticated through your email.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="shrink-0 px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                    <button 
                        type="button" 
                        onclick="closePasswordSuccessModal()" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white text-xs sm:text-sm font-bold shadow-md transition cursor-pointer flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[40px]"
                    >
                        <span>Acknowledge & Close</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Top Profile Banner & Identification Header -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 sm:gap-5 min-w-0">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gov-700 text-amber-300 border-2 border-amber-400/40 flex items-center justify-center font-black text-2xl sm:text-3xl shadow-sm shrink-0">
                {{ substr($employee->first_name ?? $user->username, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-950 truncate max-w-full">
                        {{ $employee->full_name ?? $user->username }}
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase font-mono tracking-wider
                        @if($user->role === 'admin') bg-amber-100 text-amber-900 border border-amber-300
                        @elseif($user->role === 'hrmu_staff') bg-sky-100 text-sky-900 border border-sky-300
                        @elseif($user->role === 'routing_clerk') bg-emerald-100 text-emerald-900 border border-emerald-300
                        @else bg-slate-100 text-slate-800 border border-slate-300
                        @endif">
                        {{ str_replace('_', ' ', $user->role) }}
                    </span>
                    @if($employee->employment_type)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $employee->employment_type }}
                        </span>
                    @endif
                </div>
                <div class="text-xs sm:text-sm font-semibold text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                    <span class="flex items-center gap-1.5">
                        <i class="ph ph-briefcase text-slate-700 text-base"></i>
                        <span>{{ $employee->position ?? 'Municipal Personnel' }}</span>
                    </span>
                    <span class="text-slate-300 hidden min-[360px]:inline">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="ph ph-buildings text-slate-700 text-base"></i>
                        <span>{{ $employee->office->office_name ?? 'Basud Municipal Office' }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Direct Links -->
        <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
            @if(in_array($user->role, ['employee', 'admin']))
                <a href="{{ route('employee.dashboard') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                    <i class="ph-bold ph-squares-four text-base text-slate-600"></i>
                    <span>Dashboard</span>
                </a>
            @endif
            @if(in_array($user->role, ['employee', 'admin']))
                <a href="{{ route('employee.leave-record') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                    <i class="ph-bold ph-notebook text-base text-slate-600"></i>
                    <span>Leave Record</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Main Profile Container with 2 Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Personnel Master Summary Card (Read-only highlights) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Account Overview Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-user-circle text-gov-700 text-lg"></i>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider font-mono">Profile Summary</h3>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        {{ $user->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Username</div>
                        <div class="text-sm font-bold text-slate-900 mt-0.5 font-mono">@<span>{{ $user->username }}</span></div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Official Email</div>
                        <div class="text-xs font-semibold text-slate-900 mt-0.5 truncate">{{ $user->email ?? 'Not assigned' }}</div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Full Name</div>
                        <div class="text-xs font-bold text-slate-900 mt-0.5">{{ $employee->full_name ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Position / Title</div>
                        <div class="text-xs font-semibold text-slate-800 mt-0.5">{{ $employee->position ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Department / Office</div>
                        <div class="text-xs font-semibold text-slate-800 mt-0.5 leading-snug">
                            {{ $employee->office->office_name ?? 'Basud Municipal Office' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Employment Classification</div>
                        <div class="text-xs font-semibold text-slate-800 mt-0.5">{{ $employee->employment_type ?? 'Permanent' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">Date of Assumption / Hired</div>
                        <div class="text-xs font-semibold text-slate-800 mt-0.5 font-mono">
                            {{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('F d, Y') : 'Not specified' }}
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                        <span>Account Created</span>
                        <span>{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Civil Service Compliance & Help Notice -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-amber-950">
                    <i class="ph-bold ph-shield-check text-base text-amber-700"></i>
                    <span>Personnel Record Policy</span>
                </div>
                <p class="text-[11px] leading-relaxed text-amber-900/90">
                    Any updates to your personal details will be reflected immediately across your leave applications, pre-filled Form 6 documents, and digital service records.
                </p>
            </div>
        </div>

        <!-- Column 2 & 3: Editable Information & Password Change -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Editable Personal & Employment Information -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gov-100 text-gov-800 flex items-center justify-center font-bold">
                            <i class="ph-bold ph-pencil-simple-line text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Edit Personnel Information</h2>
                            <p class="text-xs text-slate-500">Update your name, contact email, position, and office assignment</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Name Breakdown: First, Middle, Last -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                value="{{ old('first_name', $employee->first_name ?? '') }}" 
                                required 
                                class="w-full border @error('first_name') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                placeholder="e.g. Juan"
                            >
                            @error('first_name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="middle_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Middle Name
                            </label>
                            <input 
                                type="text" 
                                id="middle_name" 
                                name="middle_name" 
                                value="{{ old('middle_name', $employee->middle_name ?? '') }}" 
                                class="w-full border @error('middle_name') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                placeholder="e.g. Santos"
                            >
                            @error('middle_name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Last Name <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                value="{{ old('last_name', $employee->last_name ?? '') }}" 
                                required 
                                class="w-full border @error('last_name') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                placeholder="e.g. Dela Cruz"
                            >
                            @error('last_name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email & Position -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Official Email Address <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-envelope-simple text-base"></i>
                                </span>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', $user->email ?? '') }}" 
                                    required 
                                    class="w-full pl-10 border @error('email') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                    placeholder="personnel@basud.gov.ph"
                                >
                            </div>
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="position" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Position / Title <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-briefcase text-base"></i>
                                </span>
                                <input 
                                    type="text" 
                                    id="position" 
                                    name="position" 
                                    value="{{ old('position', $employee->position ?? '') }}" 
                                    required 
                                    class="w-full pl-10 border @error('position') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                    placeholder="e.g. Administrative Aide IV"
                                >
                            </div>
                            @error('position')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Station / Office & Date Hired -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="office_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Office / Station
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-buildings text-base"></i>
                                </span>
                                <select 
                                    id="office_id" 
                                    name="office_id" 
                                    class="w-full pl-10 border @error('office_id') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                    @foreach($offices as $office)
                                        <option value="{{ $office->office_id }}" {{ old('office_id', $employee->office_id) == $office->office_id ? 'selected' : '' }}>
                                            {{ $office->office_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('office_id')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="date_hired" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Date Hired / Assumption
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-calendar text-base"></i>
                                </span>
                                <input 
                                    type="date" 
                                    id="date_hired" 
                                    name="date_hired" 
                                    value="{{ old('date_hired', $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d') : '') }}" 
                                    class="w-full pl-10 border @error('date_hired') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                            </div>
                            @error('date_hired')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Save Action -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button 
                            type="submit" 
                            class="px-6 py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-gov-600 min-h-[40px]"
                        >
                            <i class="ph-bold ph-check text-base"></i>
                            <span>Save Information</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Change Password Section with Email Security Verification -->
            <div id="passwordChangeSection" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-400/40 text-amber-700 flex items-center justify-center font-bold">
                            <i class="ph-bold ph-shield-check text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Change Password</h2>
                            <p class="text-xs text-slate-500">Enhanced Civil Service security: Verified through your registered email</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-300 font-mono">
                        <i class="ph-bold ph-lock-key text-xs"></i>
                        <span>2FA Protected</span>
                    </span>
                </div>

                <form id="passwordChangeForm" method="POST" action="{{ route('profile.password.update') }}" class="p-6 space-y-5" onsubmit="return handlePasswordFormSubmit(event)">
                    @csrf
                    @method('PUT')

                    <!-- Client-Side & Server Validation Errors Alert -->
                    <div id="passwordValidationErrorAlert" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5">
                        <i class="ph-fill ph-warning-circle text-rose-600 text-base shrink-0 mt-0.5"></i>
                        <span id="passwordValidationErrorMessage">Please fill out all required password fields correctly.</span>
                    </div>

                    <!-- Current Password -->
                    <div>
                        <label for="current_password_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="current_password_input" 
                                name="current_password" 
                                required
                                autocomplete="current-password"
                                placeholder="Enter your current password"
                                class="w-full border @error('current_password') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                            >
                            <button 
                                type="button" 
                                onclick="toggleInputType('current_password_input', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none cursor-pointer"
                                title="Show/hide password"
                            >
                                <i class="ph ph-eye text-base"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password & Confirmation -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password_input" 
                                    name="new_password" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    placeholder="Minimum 6 characters"
                                    class="w-full border @error('new_password') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                <button 
                                    type="button" 
                                    onclick="toggleInputType('new_password_input', this)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none cursor-pointer"
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
                            <label for="new_password_confirmation_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password_confirmation_input" 
                                    name="new_password_confirmation" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    placeholder="Re-enter new password"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white"
                                >
                                <button 
                                    type="button" 
                                    onclick="toggleInputType('new_password_confirmation_input', this)" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 focus:outline-none cursor-pointer"
                                    title="Show/hide password"
                                >
                                    <i class="ph ph-eye text-base"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Email Security Verification (OTP) Section - Initially Hidden -->
                    <!-- Appears ONLY after user clicks "Verify & Update Password" and fields match -->
                    <div id="otpVerificationContainer" class="{{ $errors->has('otp_code') ? '' : 'hidden' }} p-4.5 bg-slate-50 border-2 border-amber-400/60 rounded-2xl space-y-3 animate-in fade-in zoom-in-95 duration-200">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-700 flex items-center justify-center font-bold shrink-0 mt-0.5">
                                    <i class="ph-bold ph-envelope-simple text-base"></i>
                                </div>
                                <div>
                                    <label for="otp_code_input" class="block text-xs font-bold text-slate-900 uppercase tracking-wider font-mono">
                                        Email Security Verification Code <span class="text-rose-500">*</span>
                                    </label>
                                    <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                                        A 6-digit security code was dispatched to your registered email (<strong class="text-slate-900 font-mono">{{ $user->email }}</strong>). Please check your email inbox and enter the code below.
                                    </p>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                id="btnResendOtp" 
                                onclick="triggerOtpDispatch()" 
                                class="px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 rounded-lg text-[11px] font-bold shadow-2xs transition cursor-pointer flex items-center gap-1.5 shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[32px]"
                            >
                                <i class="ph ph-arrow-clockwise text-xs"></i>
                                <span id="btnResendOtpText">Resend Code</span>
                            </button>
                        </div>

                        <!-- OTP Input with Live Status Notice -->
                        <div>
                            <div class="relative max-w-xs">
                                <input 
                                    type="text" 
                                    id="otp_code_input" 
                                    name="otp_code" 
                                    maxlength="6"
                                    value="{{ old('otp_code') }}"
                                    placeholder="Enter 6-digit code"
                                    class="w-full tracking-[0.25em] font-mono font-black text-center border @error('otp_code') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-gov-600 bg-white uppercase"
                                >
                            </div>
                            @error('otp_code')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                            <div id="otpStatusNotice" class="text-xs mt-1.5 hidden font-medium"></div>
                        </div>
                    </div>

                    <!-- Password Update Submit -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button 
                            type="submit" 
                            id="btnSubmitPasswordUpdate"
                            class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-amber-400 min-h-[40px]"
                        >
                            <i class="ph-bold ph-shield-check text-base"></i>
                            <span id="btnSubmitPasswordText">Verify & Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@section('scripts')
<script>
    let otpCooldownTimer = null;
    let isOtpStepActive = {{ $errors->has('otp_code') ? 'true' : 'false' }};

    function showPasswordAlert(msg) {
        const alertBox = document.getElementById('passwordValidationErrorAlert');
        const alertMsg = document.getElementById('passwordValidationErrorMessage');
        if (alertBox && alertMsg) {
            alertMsg.textContent = msg;
            alertBox.classList.remove('hidden');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function hidePasswordAlert() {
        const alertBox = document.getElementById('passwordValidationErrorAlert');
        if (alertBox) {
            alertBox.classList.add('hidden');
        }
    }

    function handlePasswordFormSubmit(event) {
        hidePasswordAlert();

        const currentPassword = document.getElementById('current_password_input').value.trim();
        const newPassword = document.getElementById('new_password_input').value;
        const confirmPassword = document.getElementById('new_password_confirmation_input').value;
        const otpInput = document.getElementById('otp_code_input');
        const otpContainer = document.getElementById('otpVerificationContainer');

        // Validation 1: Check Current Password
        if (!currentPassword) {
            event.preventDefault();
            showPasswordAlert('Please enter your Current Password.');
            document.getElementById('current_password_input').focus();
            return false;
        }

        // Validation 2: Check New Password length
        if (!newPassword || newPassword.length < 6) {
            event.preventDefault();
            showPasswordAlert('New Password must be at least 6 characters long.');
            document.getElementById('new_password_input').focus();
            return false;
        }

        // Validation 3: Check matching Confirmation Password
        if (newPassword !== confirmPassword) {
            event.preventDefault();
            showPasswordAlert('New Password and Confirm New Password do not match. Please verify both fields.');
            document.getElementById('new_password_confirmation_input').focus();
            return false;
        }

        // Step 1: If OTP section is not yet visible, intercept submit, request OTP from server, and show the OTP field
        if (!isOtpStepActive) {
            event.preventDefault();
            triggerOtpDispatch(true);
            return false;
        }

        // Step 2: If OTP section is already visible, ensure the user entered the 6-digit code before posting
        const otpValue = otpInput ? otpInput.value.trim() : '';
        if (!otpValue || otpValue.length !== 6) {
            event.preventDefault();
            showPasswordAlert('Please enter the 6-digit verification code sent to your registered email.');
            if (otpInput) otpInput.focus();
            return false;
        }

        // All fields verified and filled; allow form submission
        return true;
    }

    function triggerOtpDispatch(isInitial = false) {
        const btnSubmit = document.getElementById('btnSubmitPasswordUpdate');
        const btnSubmitText = document.getElementById('btnSubmitPasswordText');
        const btnResend = document.getElementById('btnResendOtp');
        const btnResendText = document.getElementById('btnResendOtpText');
        const statusNotice = document.getElementById('otpStatusNotice');
        const otpContainer = document.getElementById('otpVerificationContainer');
        const currentPassword = document.getElementById('current_password_input').value.trim();
        const newPassword = document.getElementById('new_password_input').value;
        const confirmPassword = document.getElementById('new_password_confirmation_input').value;

        if (isInitial && btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmitText.textContent = 'Sending Verification Code to Email...';
        }
        if (btnResend) {
            btnResend.disabled = true;
            btnResendText.textContent = 'Sending...';
        }

        statusNotice.classList.remove('hidden', 'text-rose-600', 'text-emerald-700');
        statusNotice.classList.add('text-slate-600');
        statusNotice.textContent = 'Sending verification code to your registered email...';

        fetch("{{ route('profile.password.otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (btnSubmit) btnSubmit.disabled = false;
            if (btnResend) btnResend.disabled = false;
            if (btnResendText) btnResendText.textContent = 'Resend Code';

            if (status === 200 && body.success) {
                // Reveal OTP Container
                isOtpStepActive = true;
                otpContainer.classList.remove('hidden');
                btnSubmitText.textContent = 'Confirm & Update Password';

                statusNotice.classList.remove('text-slate-600');
                statusNotice.classList.add('text-emerald-700');
                statusNotice.textContent = '✓ ' + body.message;

                const otpInput = document.getElementById('otp_code_input');
                if (otpInput) {
                    otpInput.value = '';
                    otpInput.focus();
                }

                // Start 60 second resend cooldown
                let secondsLeft = 60;
                if (btnResend) {
                    btnResend.disabled = true;
                    btnResendText.textContent = `Resend in ${secondsLeft}s`;
                }

                if (otpCooldownTimer) clearInterval(otpCooldownTimer);
                otpCooldownTimer = setInterval(() => {
                    secondsLeft--;
                    if (secondsLeft <= 0) {
                        clearInterval(otpCooldownTimer);
                        if (btnResend) {
                            btnResend.disabled = false;
                            btnResendText.textContent = 'Resend Code';
                        }
                    } else {
                        if (btnResend) {
                            btnResendText.textContent = `Resend in ${secondsLeft}s`;
                        }
                    }
                }, 1000);
            } else {
                if (btnSubmitText) btnSubmitText.textContent = 'Verify & Update Password';
                statusNotice.classList.remove('text-slate-600');
                statusNotice.classList.add('text-rose-600');
                const errMsg = body.message || (body.errors ? Object.values(body.errors).flat()[0] : 'Failed to send verification code.');
                statusNotice.textContent = '✕ ' + errMsg;
                showPasswordAlert(errMsg);
            }
        })
        .catch(err => {
            if (btnSubmit) btnSubmit.disabled = false;
            if (btnSubmitText) btnSubmitText.textContent = 'Verify & Update Password';
            if (btnResend) btnResend.disabled = false;
            if (btnResendText) btnResendText.textContent = 'Resend Code';
            statusNotice.classList.remove('text-slate-600');
            statusNotice.classList.add('text-rose-600');
            statusNotice.textContent = '✕ Network error while contacting server. Please try again.';
        });
    }
    function closePasswordSuccessModal() {
        const modal = document.getElementById('passwordSuccessModal');
        if (modal) {
            modal.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                modal.remove();
            }, 250);
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePasswordSuccessModal();
        }
    });
</script>
@endsection

        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Receiver QR Scanner & Reception Desk')

@section('content')
<div class="space-y-6">

    <!-- Toast Notification Container (Non-blocking feedback per UI rules) -->
    <div id="scannerToastContainer" class="fixed bottom-4 left-4 right-4 sm:left-auto z-50 flex flex-col gap-2 sm:max-w-sm sm:w-full pointer-events-none"></div>

    <!-- Station Location Header -->
    <div class="bg-white rounded-xl p-3.5 sm:p-6 border border-slate-200 shadow-xs flex flex-col xl:flex-row xl:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0 flex-1">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gov-50 text-gov-700 border border-gov-200 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                <i class="ph ph-barcode"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <h1 class="text-base sm:text-xl font-bold text-slate-900 leading-tight">Document Physical Custody Terminal</h1>
                    @if($isMayorOffice)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:py-1 rounded-md text-[11px] sm:text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                            <i class="ph ph-seal-check text-sm sm:text-base"></i>
                            <span>Appointing Authority</span>
                        </span>
                    @endif
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>Station: <strong class="text-slate-800">{{ $currentOffice->office_name }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Station Office & Operator Selector -->
        @if(Auth::user()->role === 'employee')
            <!-- Locked Station Badge for Standard Employees (Personal Document Tracking Mode) -->
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono">
                <i class="ph-bold ph-user-circle text-lg text-amber-600"></i>
                <div class="text-left">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Personal Tracking Terminal</div>
                    <div class="font-bold text-slate-800">{{ Auth::user()->employee->full_name ?? Auth::user()->username }}</div>
                </div>
            </div>
        @else
            <form method="GET" action="{{ route('clerk.scanner') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto shrink-0 pt-4 xl:pt-0 border-t xl:border-t-0 border-slate-100">
                <div class="flex flex-col gap-1 min-w-0">
                    <label for="station_select" class="text-xs text-slate-500 font-medium">Station Office</label>
                    <select id="station_select" name="office_id" onchange="this.form.submit()" class="w-full xl:w-60 text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-gov-600 truncate min-h-[40px]">
                        @foreach($offices as $off)
                            <option value="{{ $off->office_id }}" {{ $off->office_id == $currentOffice->office_id ? 'selected' : '' }}>
                                {{ $off->office_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="clerk_select" class="text-xs text-slate-500 font-medium">Receiver / Staff on Duty</label>
                    <select id="clerk_select" name="user_id" onchange="this.form.submit()" class="w-full xl:w-64 text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-gov-600 truncate min-h-[40px]">
                        @foreach($authorizedUsers as $authUser)
                            <option value="{{ $authUser->user_id }}" {{ $currentClerk && $currentClerk->user_id == $authUser->user_id ? 'selected' : '' }}>
                                {{ $authUser->employee?->full_name ?? $authUser->username }} ({{ $authUser->employee?->office?->office_name ?? ucfirst(str_replace('_', ' ', $authUser->role)) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif
    </div>

    <!-- Main Two-Column Responsive Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Main Scanner & Reception Form -->
        <div class="lg:col-span-7 bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="bg-gov-navy text-white px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-800 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="ph ph-qr-code text-xl"></i>
                    <span class="text-xs sm:text-sm font-bold tracking-wide uppercase">Rapid Barcode / Unified Input Scanner</span>
                </div>
            </div>

            <form id="routingForm" onsubmit="event.preventDefault(); lookupReference();" class="p-4 sm:p-6 space-y-5 sm:space-y-6">
                @csrf
                <input type="hidden" id="office_id" name="office_id" value="{{ $currentOffice->office_id }}">
                <input type="hidden" id="clerk_user_id" name="user_id" value="{{ $currentClerk->user_id ?? '' }}">
                <input type="hidden" id="resolved_application_id" name="application_id" value="">

                <!-- 1. Large Autofocus Reference Code Input with Multi-Mode Scanner Options -->
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <label for="reference_code" class="block text-xs font-bold text-slate-900 uppercase tracking-wider">
                            Scan QR or Enter Reference Code <span class="text-rose-600">*</span>
                        </label>
                        <button 
                            type="button" 
                            id="btnToggleCamera" 
                            onclick="toggleCameraScanner()" 
                            class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 rounded-lg text-xs font-semibold transition cursor-pointer min-h-[36px]"
                        >
                            <i class="ph ph-camera text-base"></i>
                            <span id="cameraBtnText">Scan with Camera / Webcam</span>
                        </button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none text-slate-400 text-lg sm:text-xl">
                            <i class="ph ph-qr-code"></i>
                        </div>
                        <input 
                            type="text" 
                            id="reference_code" 
                            name="reference_code" 
                            autofocus 
                            autocomplete="off"
                            placeholder="e.g. LV-2026-0001 (Press Enter to Look Up)" 
                            onkeydown="handleReferenceKeydown(event)"
                            oninput="handleReferenceInput()"
                            class="w-full pl-10 sm:pl-12 pr-24 sm:pr-28 py-3 text-sm sm:text-lg font-mono font-bold text-slate-900 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-gov-600 tracking-wider placeholder:font-sans placeholder:text-xs sm:placeholder:text-sm placeholder:text-slate-400 placeholder:font-normal min-h-[44px]"
                            required
                        >
                        <button 
                            type="button" 
                            onclick="lookupReference()" 
                            class="absolute inset-y-1.5 sm:inset-y-2 right-1.5 sm:right-2 px-3 sm:px-4 bg-gov-50 hover:bg-gov-100 text-gov-800 border border-gov-200 rounded-lg text-xs font-semibold transition flex items-center gap-1 focus:outline-none focus:ring-2 focus:ring-gov-600 cursor-pointer"
                        >
                            <i class="ph ph-magnifying-glass text-sm sm:text-base"></i>
                            <span>Lookup</span>
                        </button>
                    </div>
                </div>

                <!-- Live Camera Viewfinder Box (Toggled on demand) -->
                <div id="cameraScannerCard" class="hidden bg-slate-900 text-white rounded-xl p-4 border border-slate-700 space-y-4 shadow-sm">
                    <!-- Header with Status, Camera Chooser, and Close button -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <div>
                                <span class="text-xs font-bold text-white uppercase tracking-wider block">Live QR Camera Scanner</span>
                                <span id="cameraStatusText" class="text-xs text-slate-400">Initializing camera feed...</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Camera Selector Dropdown (for Multi-Camera Laptops/Phones) -->
                            <div id="cameraSelectContainer" class="hidden flex items-center gap-2">
                                <label for="cameraSelect" class="text-xs text-slate-400">Lens:</label>
                                <select id="cameraSelect" onchange="switchCamera(this.value)" class="text-xs bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-gov-600 max-w-40 truncate">
                                </select>
                            </div>

                            <!-- Close Button -->
                            <button type="button" onclick="stopCameraScanner()" class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-1 rounded-lg bg-slate-800 hover:bg-rose-900 border border-slate-700 transition cursor-pointer flex items-center gap-1">
                                <i class="ph ph-x text-base"></i>
                                <span>Close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Video Viewfinder Frame -->
                    <div id="cameraPreviewBox" class="relative overflow-hidden rounded-xl bg-black border border-slate-700 flex items-center justify-center min-h-64 transition-all">
                        <div id="qr-reader" class="w-full"></div>

                        <!-- Target Overlay Frame Indicator -->
                        <div id="targetOverlayBox" class="pointer-events-none absolute inset-0 flex items-center justify-center">
                            <div class="w-44 h-44 sm:w-56 sm:h-56 border-2 border-dashed border-white/70 rounded-xl relative flex items-center justify-center">
                                <div class="w-full h-1 bg-gov-600/80 animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Guidance & Fallback Options -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-lightbulb text-base shrink-0"></i>
                            <span>Tip: Hold the QR slip steady about <strong>15–25 cm</strong> from the lens.</span>
                        </div>

                        <!-- Photo Upload Fallback -->
                        <div class="flex items-center gap-2">
                            <input type="file" id="qrFileInput" accept="image/*" class="hidden" onchange="handleQrFileUpload(event)">
                            <button type="button" onclick="document.getElementById('qrFileInput').click()" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1 transition cursor-pointer" title="Scan QR from saved photo or screenshot">
                                <i class="ph ph-image text-base"></i>
                                <span>Upload QR Photo</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Application Preview Card (Appears upon Scan / Lookup) -->
                <div id="previewCard" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-4 transition-all">
                    <div class="mb-3 pb-3 border-b border-slate-200 flex items-center justify-between gap-2 text-xs text-gov-800">
                        <span class="font-semibold flex items-center gap-1">
                            <i class="ph ph-eye text-base"></i>
                            <span>Document Preview Only — Verify details below before clicking "Update Routing Log"</span>
                        </span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-base font-bold text-gov-900" id="previewRefCode">LV-2026-XXXX</span>
                                <span id="previewStatusBadge" class="px-2 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pending</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 mt-1" id="previewEmpName">Employee Name</h4>
                            <div class="text-xs text-slate-500" id="previewEmpDetails">Position • Office Assignment</div>
                        </div>
                        <div class="sm:text-right shrink-0">
                            <div class="text-xs text-slate-400">Leave Applied</div>
                            <div class="text-sm font-semibold text-slate-800" id="previewLeaveType">Vacation Leave (3 Days)</div>
                            <div class="text-xs text-slate-500 font-mono" id="previewDates">Oct 12-14, 2026</div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1 text-slate-600">
                            <i class="ph ph-map-pin text-base"></i>
                            <span>Last Known Location: <strong id="previewLocation" class="text-slate-900">HRMU</strong></span>
                        </div>
                        <div id="previewSla">
                            <!-- SLA badge -->
                        </div>
                    </div>

                    <!-- Section 7.A Leave Credits Certification Status & HRMU Desk Controls -->
                    <div id="section7APanel" class="mt-3 pt-3 border-t border-slate-200">
                        <!-- Dynamic Section 7.A Panel populated via JavaScript -->
                    </div>

                    <!-- Digital Supporting Documents & Attachments Verification Panel -->
                    <div id="attachmentsVerificationPanel" class="mt-3 pt-3 border-t border-slate-200">
                        <!-- Dynamic Attachments Verification Panel populated via JavaScript -->
                    </div>
                </div>

                <!-- Error message if not found -->
                <div id="lookupError" class="hidden bg-rose-50 border border-rose-300 text-rose-800 p-3 rounded-xl text-xs font-semibold flex items-center gap-2">
                    <i class="ph ph-warning-circle text-base shrink-0"></i>
                    <span id="lookupErrorText">Application not found in system.</span>
                </div>

                <!-- Dynamic Routing Completed / Station Finished Card -->
                <div id="routingCompletedCard" class="hidden"></div>

                <div id="actionInputsContainer" class="space-y-4">
                    <!-- 2. Action Dropdown Selection (Office-Based Governance) -->
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <label for="action_select" class="block text-xs font-bold text-slate-900 uppercase tracking-wider">
                                Routing Action to Record <span class="text-rose-600">*</span>
                            </label>
                            <span id="suggestedActionBadge" class="hidden text-xs font-semibold px-2 py-1 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300 items-center gap-1">
                                <i class="ph ph-sparkle text-base"></i>
                                <span>Recommended Next Action</span>
                            </span>
                        </div>

                        <select 
                            id="action_select" 
                            name="action" 
                            required 
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-gov-600 bg-white"
                        >
                            <option value="" disabled selected>-- Select Routing Action to Execute --</option>
                            @foreach($allowedActions as $actKey => $actLabel)
                                <option value="{{ $actKey }}">
                                    {{ $actLabel }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Dynamic Reason / Workflow Direction Helper -->
                        <div id="suggestedActionHint" class="hidden mt-2 p-3 bg-gov-50 border border-gov-200 rounded-xl text-xs text-gov-900 flex items-start gap-2">
                            <i class="ph ph-info text-base shrink-0 mt-1"></i>
                            <div>
                                <div class="font-bold text-gov-900">Next Required Action:</div>
                                <div id="suggestedActionText" class="text-gov-800 mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Optional Remarks / Routing Notes -->
                    <div>
                        <label for="remarks" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Routing Notes / Remarks (Optional)
                        </label>
                        <input 
                            type="text" 
                            id="remarks" 
                            name="remarks" 
                            onkeydown="if(event.key === 'Enter'){ event.preventDefault(); }"
                            placeholder="Enter notes or remarks"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-gov-600"
                        >
                    </div>
                </div>

                <!-- 4. Large Update Routing Log Button (Explicit Click Only) -->
                <button 
                    type="button" 
                    id="submitBtn" 
                    onclick="handleFormSubmit(event)"
                    disabled
                    class="w-full py-3.5 px-4 bg-slate-200 text-slate-500 border border-slate-300 rounded-xl text-sm sm:text-base font-bold transition flex items-center justify-center gap-2 cursor-not-allowed select-none min-h-[48px]"
                >
                    <i class="ph ph-magnifying-glass text-xl"></i>
                    <span>Scan or Look Up a Document First</span>
                </button>
            </form>
        </div>

        <!-- Right Column: Live Recent Desk Scans Table -->
        <div class="lg:col-span-5 bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="ph ph-clock-counter-clockwise text-base"></i>
                    <span>Recent Scans at this Terminal</span>
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-xs text-slate-700" id="recentScansList">
                @forelse($recentLogs as $log)
                    <div 
                        onclick="fillReference('{{ $log->leaveApplication->reference_code ?? '' }}')" 
                        class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition cursor-pointer group"
                        title="Click to load {{ $log->leaveApplication->reference_code ?? '' }} in scanner"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gov-800 text-sm group-hover:underline">{{ $log->leaveApplication->reference_code ?? 'N/A' }}</span>
                                <span class="font-semibold text-slate-900 truncate">{{ $log->leaveApplication->employee->full_name ?? 'Employee' }}</span>
                            </div>
                            <div class="text-xs text-slate-400 truncate mt-1">
                                {{ $log->office->office_name ?? 'Office' }} • {{ $log->scanner?->employee?->full_name ?? $log->scanner?->employee?->employee_no ?? 'Receiver' }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ str_contains($log->action, 'APPROVED') ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : (str_contains($log->action, 'DISAPPROVED') ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-800 border border-slate-200') }}">
                                {{ $log->action }}
                            </span>
                            <div class="text-xs text-slate-400 font-mono mt-1">{{ $log->scanned_at->format('M d, h:i A') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        No recent scans recorded today. Ready for hardware or manual lookup.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<!-- Local high-performance QR code scanner engine -->
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

<script>
    let lookupTimeout = null;
    let currentLoadedApp = null;
    let html5QrCode = null;
    let availableCameras = [];
    let currentCameraId = null;
    let isCameraActive = false;

    function showToast(message, type = 'success') {
        const container = document.getElementById('scannerToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        const styles = {
            success: 'bg-emerald-50 border-emerald-300 text-emerald-900',
            error: 'bg-rose-50 border-rose-300 text-rose-900',
            warning: 'bg-amber-50 border-amber-300 text-amber-900',
            info: 'bg-slate-900 border-slate-700 text-white'
        };
        const icons = {
            success: 'ph-check-circle text-emerald-600',
            error: 'ph-warning-circle text-rose-600',
            warning: 'ph-warning text-amber-600',
            info: 'ph-info text-slate-300'
        };

        toast.className = `pointer-events-auto border rounded-xl p-4 shadow-lg flex items-start justify-between gap-3 transition-all duration-200 ${styles[type] || styles.info}`;
        toast.innerHTML = `
            <div class="flex items-start gap-2 text-xs font-semibold leading-relaxed">
                <i class="ph ${icons[type] || icons.info} text-base shrink-0 mt-1"></i>
                <span>${message}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 p-1 rounded cursor-pointer shrink-0">
                <i class="ph ph-x text-base"></i>
            </button>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.remove();
            }
        }, 4500);
    }

    async function toggleCameraScanner() {
        if (isCameraActive) {
            stopCameraScanner();
            return;
        }
        await startCameraScanner();
    }

    async function startCameraScanner(specificCameraId = null) {
        const card = document.getElementById('cameraScannerCard');
        if (!card) return;
        card.classList.remove('hidden');

        const btnText = document.getElementById('cameraBtnText');
        if (btnText) btnText.innerText = 'Stop Camera';
        isCameraActive = true;

        const statusText = document.getElementById('cameraStatusText');
        if (statusText) statusText.innerText = 'Requesting camera permissions...';

        try {
            // Stop any existing instance
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (e) {}
                html5QrCode = null;
            }

            // Enumerate camera devices
            if (!availableCameras.length) {
                try {
                    availableCameras = await Html5Qrcode.getCameras();
                } catch (e) {
                    console.warn('Could not list cameras beforehand:', e);
                }
            }

            const selectContainer = document.getElementById('cameraSelectContainer');
            const selectEl = document.getElementById('cameraSelect');

            if (availableCameras && availableCameras.length > 0) {
                if (selectContainer && selectEl) {
                    selectEl.innerHTML = '';
                    availableCameras.forEach((cam, idx) => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.innerText = cam.label || `Camera ${idx + 1}`;
                        selectEl.appendChild(opt);
                    });
                    selectContainer.classList.remove('hidden');
                }

                // Choose camera: prefer selected, or rear camera, or first available
                if (specificCameraId) {
                    currentCameraId = specificCameraId;
                } else if (!currentCameraId) {
                    const backCam = availableCameras.find(c => /back|rear|environment/i.test(c.label));
                    currentCameraId = backCam ? backCam.id : availableCameras[0].id;
                }
                if (selectEl) selectEl.value = currentCameraId;
            }

            // Create scanner instance with barcode detector optimization
            html5QrCode = new Html5Qrcode("qr-reader", {
                verbose: false,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            });

            // Responsive scanning box covering 85% of camera view so QR can be anywhere
            const config = {
                fps: 20,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const size = Math.max(180, Math.floor(minEdge * 0.85));
                    return { width: size, height: size };
                },
                aspectRatio: 1.333333
            };

            const cameraParam = currentCameraId ? currentCameraId : { facingMode: "user" };

            await html5QrCode.start(
                cameraParam,
                config,
                (decodedText) => {
                    handleCameraScanSuccess(decodedText);
                },
                (errorMessage) => {
                    // Frame scan attempt without detection (normal per frame)
                }
            );

            if (statusText) {
                statusText.innerHTML = '<span class="text-emerald-400 font-semibold">● Camera Active — Ready to scan</span>';
            }

        } catch (err) {
            console.error('Camera initialization error:', err);
            if (statusText) {
                statusText.innerText = 'Camera access notice: ' + (err.message || 'Check camera');
            }
            showToast('Camera notice: ' + (err.message || 'Please ensure camera access is allowed in your browser. You can also upload a QR photo or enter code manually.'), 'warning');
            stopCameraScanner();
        }
    }

    async function switchCamera(newCameraId) {
        if (!newCameraId || newCameraId === currentCameraId) return;
        currentCameraId = newCameraId;
        await startCameraScanner(newCameraId);
    }

    function stopCameraScanner() {
        const card = document.getElementById('cameraScannerCard');
        if (card) card.classList.add('hidden');
        const btnText = document.getElementById('cameraBtnText');
        if (btnText) btnText.innerText = 'Scan with Camera / Webcam';
        isCameraActive = false;

        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            }).catch((err) => {
                console.warn('Error stopping scanner:', err);
                html5QrCode = null;
            });
        }
    }

    function handleCameraScanSuccess(decodedText) {
        if (!decodedText) return;
        
        let cleanCode = decodedText.trim();

        // Flash target box green
        const box = document.getElementById('cameraPreviewBox');
        if (box) {
            box.classList.add('ring-4', 'ring-emerald-400');
            setTimeout(() => box.classList.remove('ring-4', 'ring-emerald-400'), 1500);
        }

        // Extract reference code if formatted like LV-2026-XXXX or inside URL
        const match = cleanCode.match(/LV-\d{4}-\d+/i);
        if (match) {
            cleanCode = match[0].toUpperCase();
        }

        const statusText = document.getElementById('cameraStatusText');
        if (statusText) {
            statusText.innerHTML = `<span class="text-emerald-300 font-bold">✔ Code Detected: ${cleanCode}</span>`;
        }

        const input = document.getElementById('reference_code');
        input.value = cleanCode;

        // Stop camera and trigger read-only application lookup (never auto-updates routing log)
        stopCameraScanner();
        showToast(`QR code ${cleanCode} detected. Review the file details below and click "Update Routing Log" to confirm.`, 'info');
        lookupReference();
    }

    async function handleQrFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        try {
            const scanner = new Html5Qrcode("qr-reader");
            const decodedText = await scanner.scanFile(file, true);
            handleCameraScanSuccess(decodedText);
        } catch (err) {
            console.error('File QR Scan Error:', err);
            showToast('Could not detect a readable QR code in this image. Please upload a clear photo of the CS Form 6 QR slip.', 'error');
        } finally {
            event.target.value = '';
        }
    }

    function fillReference(code) {
        if (!code || code === 'N/A') return;
        const input = document.getElementById('reference_code');
        input.value = code;
        showToast(`Loaded ${code} for preview. Review details before clicking "Update Routing Log".`, 'info');
        lookupReference();
    }

    function handleReferenceKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            clearTimeout(lookupTimeout);
            lookupReference();
        }
    }

    function handleReferenceInput() {
        clearTimeout(lookupTimeout);
        const code = document.getElementById('reference_code').value.trim();

        // Reset resolved state if the input changed from the currently previewed application
        if (!currentLoadedApp || currentLoadedApp.reference_code.toUpperCase() !== code.toUpperCase()) {
            document.getElementById('resolved_application_id').value = '';
            currentLoadedApp = null;
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.className = 'w-full py-3 px-4 bg-slate-200 text-slate-500 border border-slate-300 rounded-xl text-sm sm:text-base font-bold transition flex items-center justify-center gap-2 cursor-not-allowed select-none';
                submitBtn.innerHTML = '<i class="ph ph-magnifying-glass text-xl"></i> <span>Scan or Look Up a Document First</span>';
            }
        }

        // Auto-lookup preview only (never submits routing status)
        if (code.length >= 8) {
            lookupTimeout = setTimeout(() => {
                lookupReference();
            }, 350);
        }
    }

    async function lookupReference() {
        const codeInput = document.getElementById('reference_code');
        const code = codeInput.value.trim();
        if (!code) return;

        const officeId = document.getElementById('office_id').value;
        const submitBtn = document.getElementById('submitBtn');

        document.getElementById('lookupError').classList.add('hidden');
        const badge = document.getElementById('suggestedActionBadge');
        if (badge) {
            badge.classList.add('hidden');
            badge.classList.remove('inline-flex');
        }
        const hint = document.getElementById('suggestedActionHint');
        if (hint) {
            hint.classList.add('hidden');
        }

        try {
            const res = await fetch(`/api/routing/scan?reference_code=${encodeURIComponent(code)}&office_id=${encodeURIComponent(officeId)}`);
            const json = await res.json();

            if (json.success) {
                const app = json.data;
                document.getElementById('resolved_application_id').value = app.application_id;
                document.getElementById('previewRefCode').innerText = app.reference_code;
                document.getElementById('previewStatusBadge').innerText = app.status;
                document.getElementById('previewEmpName').innerText = app.employee.full_name;
                document.getElementById('previewEmpDetails').innerText = `${app.employee.position} • ${app.employee.office_name}`;
                document.getElementById('previewLeaveType').innerText = `${app.leave_details.leave_type} (${app.leave_details.days_requested} Days)`;
                document.getElementById('previewDates').innerText = `${app.leave_details.date_from} to ${app.leave_details.date_to}`;
                document.getElementById('previewLocation').innerText = app.current_location ? app.current_location.office_name : 'In Transit';

                // Render SLA Tracker status
                const slaContainer = document.getElementById('previewSla');
                if (slaContainer && app.sla_tracker) {
                    const sla = app.sla_tracker;
                    let badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    let text = `${sla.working_days_elapsed}/5 Days Elapsed`;
                    if (sla.compliance_status === 'WARNING') {
                        badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                    } else if (sla.compliance_status === 'BREACHED') {
                        badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                        text = `${sla.working_days_elapsed}/5 Days (Sec 49 SLA Risk)`;
                    }
                    slaContainer.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold border ${badgeClass}"><i class="ph ph-timer text-base"></i><span>${text}</span></span>`;
                }

                // Render Section 7.A (Certification of Leave Credits) Panel
                const certPanel = document.getElementById('section7APanel');
                const cert = app.certification;
                if (certPanel && cert) {
                    if (cert.is_certified) {
                        certPanel.innerHTML = `
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2 font-bold text-emerald-900">
                                        <i class="ph ph-seal-check text-base"></i>
                                        <span>Section 7.A: Certified by HRMU</span>
                                    </span>
                                    <a href="/forms/form-6/${app.application_id}/print" target="_blank" class="text-gov-700 hover:text-gov-900 font-semibold underline flex items-center gap-1">
                                        <i class="ph ph-printer text-base"></i>
                                        <span>View Pre-Filled Form 6</span>
                                    </a>
                                </div>
                                <div class="text-slate-600 mt-1">
                                    Certified as of <strong>${cert.certified_as_of || 'Verified'}</strong> by <strong>${cert.certified_by || 'HRMO II'}</strong>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 pt-2 border-t border-emerald-200 font-mono text-xs">
                                    <div class="bg-white p-2 rounded-lg border border-emerald-100">
                                        <span class="text-slate-500 font-sans block">Vacation Leave:</span>
                                        <span class="text-slate-800 font-bold">${Number(cert.vl_earned).toFixed(3)} - ${Number(cert.vl_less).toFixed(3)} = <span class="text-emerald-700">${Number(cert.vl_balance).toFixed(3)}</span></span>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-emerald-100">
                                        <span class="text-slate-500 font-sans block">Sick Leave:</span>
                                        <span class="text-slate-800 font-bold">${Number(cert.sl_earned).toFixed(3)} - ${Number(cert.sl_less).toFixed(3)} = <span class="text-emerald-700">${Number(cert.sl_balance).toFixed(3)}</span></span>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (app.is_hrmu_office) {
                        const live = cert.system_live_balances;
                        // Only HRMU staff or System Administrators can certify Section 7.A
                        const canCertify7A = @json(in_array(Auth::user()->role ?? '', ['hrmu_staff', 'admin']));
                        const certifyBtnHtml = canCertify7A ? `
                            <button 
                                type="button" 
                                onclick="certifySection7A(${app.application_id})"
                                id="certifyBtn_${app.application_id}"
                                class="px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs flex items-center gap-2 cursor-pointer shadow-xs transition"
                            >
                                <i class="ph ph-stamp text-base"></i>
                                <span>Certify Section 7.A Now</span>
                            </button>
                        ` : `
                            <span class="text-xs font-semibold text-amber-800 bg-amber-100 border border-amber-300 px-2 py-1 rounded-md">
                                Requires HR Staff / Admin to Certify
                            </span>
                        `;
                        certPanel.innerHTML = `
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2 font-bold text-amber-900">
                                        <i class="ph ph-clock text-base"></i>
                                        <span>Section 7.A: Uncertified (Pending HRMU Certification)</span>
                                    </span>
                                    <span class="text-xs text-amber-800 font-medium px-2 py-1 rounded-md bg-amber-100 border border-amber-300">
                                        Blank on Employee Submission
                                    </span>
                                </div>
                                <p class="text-slate-600 text-xs mt-1 leading-snug">
                                    Civil Service Omnibus Rule XVI: Section 7.A was printed blank by the applicant. HRMU certifies official leave balances before forwarding to Mayor.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 pt-2 border-t border-amber-200 font-mono text-xs">
                                    <div class="bg-white p-2 rounded-lg border border-amber-200">
                                        <span class="text-slate-500 font-sans block">Vacation Leave (Ledger):</span>
                                        <span class="text-slate-800 font-bold">${Number(live.vl_earned).toFixed(3)} - ${Number(live.vl_less).toFixed(3)} = <span class="text-gov-800">${Number(live.vl_balance).toFixed(3)}</span></span>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border border-amber-200">
                                        <span class="text-slate-500 font-sans block">Sick Leave (Ledger):</span>
                                        <span class="text-slate-800 font-bold">${Number(live.sl_earned).toFixed(3)} - ${Number(live.sl_less).toFixed(3)} = <span class="text-gov-800">${Number(live.sl_balance).toFixed(3)}</span></span>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs text-slate-500 font-mono">As of: ${live.as_of_formatted}</span>
                                    ${certifyBtnHtml}
                                </div>
                            </div>
                        `;
                    } else {
                        certPanel.innerHTML = `
                            <div class="bg-slate-100 border border-slate-200 rounded-xl p-3 text-xs text-slate-600 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-shield text-base"></i>
                                    <div>
                                        <div class="font-bold text-slate-800">Section 7.A: Blank</div>
                                        <div class="text-xs text-slate-500">Remains blank during employee filing & dept recommendation; certified upon arrival at HRMU.</div>
                                    </div>
                                </div>
                                <span class="text-xs font-mono font-semibold px-2 py-1 rounded-md bg-slate-200 text-slate-700">
                                    Awaiting HRMU
                                </span>
                            </div>
                        `;
                    }
                }

                // Render Digital Supporting Documents Panel (View & Download Only)
                const attPanel = document.getElementById('attachmentsVerificationPanel');
                if (attPanel) {
                    const attachments = app.attachments || [];

                    if (attachments.length > 0) {
                        attPanel.innerHTML = `
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 font-extrabold uppercase tracking-wider text-slate-800">
                                        <i class="ph-bold ph-paperclip text-base text-gov-700"></i>
                                        <span>Digital Supporting Documents (${attachments.length})</span>
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500">CSC Form No. 6 Attachments</span>
                                </div>
                                <div class="space-y-2">
                                    ${attachments.map(att => {
                                        return `
                                            <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                                                <div class="min-w-0 flex-1">
                                                    <span class="font-bold text-slate-900 text-xs">${att.document_label}</span>
                                                    <div class="text-[11px] text-slate-600 truncate mt-0.5 flex items-center gap-1.5">
                                                        <i class="${att.is_pdf ? 'ph-bold ph-file-pdf text-rose-600' : 'ph-bold ph-file-image text-blue-600'} text-sm"></i>
                                                        <span class="font-semibold text-slate-900 truncate">${att.original_filename}</span>
                                                        <span class="text-slate-400 font-mono">(${att.size_formatted})</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <a href="${att.view_url}" target="_blank" class="p-2 text-slate-700 hover:text-gov-800 hover:bg-slate-100 rounded-lg border border-slate-300 transition inline-flex items-center justify-center" title="View Document">
                                                        <i class="ph ph-eye text-base"></i>
                                                    </a>
                                                    <a href="${att.download_url}" class="p-2 text-slate-700 hover:text-gov-800 hover:bg-slate-100 rounded-lg border border-slate-300 transition inline-flex items-center justify-center" title="Download Document">
                                                        <i class="ph ph-download-simple text-base"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        `;
                    } else {
                        attPanel.innerHTML = `
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-600 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 font-semibold">
                                    <i class="ph ph-paperclip text-base text-slate-400"></i>
                                    <span>Digital Attachments: None uploaded</span>
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono">Routine or Exempt under CSC Rules</span>
                            </div>
                        `;
                    }
                }

                document.getElementById('previewCard').classList.remove('hidden');
                currentLoadedApp = app;

                // Render workflow state: locks dropdown and button if done, or configures next action
                renderWorkflowState(app);
            } else {
                document.getElementById('resolved_application_id').value = '';
                currentLoadedApp = null;
                document.getElementById('previewCard').classList.add('hidden');
                document.getElementById('lookupErrorText').innerText = json.message || 'Application not found.';
                document.getElementById('lookupError').classList.remove('hidden');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.className = 'w-full py-3 px-4 bg-slate-200 text-slate-500 border border-slate-300 rounded-xl text-sm sm:text-base font-bold transition flex items-center justify-center gap-2 cursor-not-allowed select-none';
                    submitBtn.innerHTML = '<i class="ph ph-magnifying-glass text-xl"></i> <span>Scan or Look Up a Document First</span>';
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    function renderWorkflowState(app) {
        const completedCard = document.getElementById('routingCompletedCard');
        const actionContainer = document.getElementById('actionInputsContainer');
        const actionSelect = document.getElementById('action_select');
        const submitBtn = document.getElementById('submitBtn');
        const badge = document.getElementById('suggestedActionBadge');
        const hint = document.getElementById('suggestedActionHint');

        if (!app.can_act) {
            // Stop the user from clicking anymore
            if (actionContainer) actionContainer.classList.add('hidden');
            if (actionSelect) actionSelect.removeAttribute('required');
            if (badge) badge.classList.add('hidden');
            if (hint) hint.classList.add('hidden');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.className = 'w-full py-3 px-4 bg-slate-200 text-slate-500 rounded-xl text-sm sm:text-base font-bold border border-slate-300 flex items-center justify-center gap-2 cursor-not-allowed opacity-90 select-none';
                submitBtn.innerHTML = '<i class="ph ph-lock-key text-xl"></i> <span>Routing Actions Finished — Locked</span>';
            }

            if (completedCard) {
                completedCard.classList.remove('hidden');

                const isFull = (app.completion_type === 'FULLY_COMPLETED' || app.is_completed);
                const isCancelled = (app.completion_type === 'CANCELLED' || app.status === 'Cancelled');

                if (isCancelled) {
                    completedCard.innerHTML = `
                        <div class="bg-rose-50 border border-rose-300 rounded-xl p-4 shadow-xs text-rose-950">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <i class="ph ph-x-circle text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold bg-rose-700 text-white uppercase tracking-wider">
                                            <i class="ph ph-prohibit text-base"></i> Cancelled
                                        </span>
                                        <span class="text-xs font-mono font-bold text-rose-900">${app.reference_code}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-950 mt-1">Application Cancelled</h4>
                                    <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                                        ${app.suggested_reason || 'This leave application has been cancelled. No further physical routing actions are permitted.'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (isFull) {
                    completedCard.innerHTML = `
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-xs text-slate-900">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <i class="ph ph-seal-check text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold bg-emerald-700 text-white uppercase tracking-wider">
                                            <i class="ph ph-check-circle text-base"></i> Routing Complete
                                        </span>
                                        <span class="text-xs font-mono font-bold text-emerald-900">${app.reference_code}</span>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            ${app.status}
                                        </span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-950 mt-2">
                                        All Routing Actions Finished
                                    </h4>
                                    <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                                        ${app.suggested_reason || 'All required physical routing checkpoints have been completed. This document was officially acted upon by the Municipal Mayor and permanently received & filed in HRMU records.'}
                                    </p>

                                    <!-- 4 Checkpoints summary -->
                                    <div class="mt-3 pt-3 border-t border-emerald-200 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                        <div class="bg-white p-2 rounded-lg border border-emerald-200">
                                            <div class="text-emerald-700 font-bold flex items-center gap-1">
                                                <i class="ph ph-check-circle text-base"></i> Dept Head
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">Recommended</div>
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-emerald-200">
                                            <div class="text-emerald-700 font-bold flex items-center gap-1">
                                                <i class="ph ph-check-circle text-base"></i> HRMU Desk
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">Box 7.A Certified</div>
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-emerald-200">
                                            <div class="text-emerald-700 font-bold flex items-center gap-1">
                                                <i class="ph ph-check-circle text-base"></i> Municipal Mayor
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">Physical Signature</div>
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-emerald-200">
                                            <div class="text-emerald-700 font-bold flex items-center gap-1">
                                                <i class="ph ph-check-circle text-base"></i> HR Archive
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">Ledger Recorded</div>
                                        </div>
                                    </div>

                                    <!-- Action Links -->
                                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                                        <a 
                                            href="/forms/form-6/${app.application_id}/print" 
                                            target="_blank" 
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold shadow-xs transition"
                                        >
                                            <i class="ph ph-printer text-base"></i>
                                            <span>Print Completed CS Form 6</span>
                                        </a>
                                        <a 
                                            href="/hr/leave-records" 
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-xs transition"
                                        >
                                            <i class="ph ph-table text-base"></i>
                                            <span>View Digital Leave Ledger</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // STATION_COMPLETED
                    completedCard.innerHTML = `
                        <div class="bg-gov-50 border border-gov-200 rounded-xl p-4 shadow-xs text-slate-900">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gov-700 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <i class="ph ph-paper-plane-tilt text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold bg-gov-700 text-white uppercase tracking-wider">
                                            <i class="ph ph-check-circle text-base"></i> Station Actions Finished
                                        </span>
                                        <span class="text-xs font-mono font-bold text-gov-900">${app.reference_code}</span>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-md bg-gov-100 text-gov-800 border border-gov-200">
                                            ${app.status}
                                        </span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-950 mt-2">
                                        ${app.workflow_state?.title || 'Desk Routing Actions Finished'}
                                    </h4>
                                    <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                                        ${app.suggested_reason || 'All required routing actions for your desk have been completed and dispatched. No further action is required at this station.'}
                                    </p>
                                    <div class="mt-3 p-3 bg-white rounded-xl border border-gov-200 text-xs text-slate-600 flex items-center gap-2">
                                        <i class="ph ph-info text-base shrink-0"></i>
                                        <span>The physical document is en route or undergoing review at the next destination. Routing actions at this desk are locked to prevent premature or duplicate records.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
        } else {
            // Can act: Show action inputs and enable submit button for explicit user click
            if (completedCard) completedCard.classList.add('hidden');
            if (actionContainer) actionContainer.classList.remove('hidden');
            if (actionSelect) actionSelect.setAttribute('required', 'required');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.className = 'w-full py-3 px-4 bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white rounded-xl text-sm sm:text-base font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 focus:ring-offset-2';
                submitBtn.innerHTML = '<i class="ph ph-paper-plane-right text-xl"></i> <span>Update Routing Log</span>';
            }

            // Populate allowed actions
            if (actionSelect && app.allowed_actions) {
                actionSelect.innerHTML = '<option value="" disabled selected>-- Select Routing Action to Execute --</option>';
                for (const [key, label] of Object.entries(app.allowed_actions)) {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = label;
                    actionSelect.appendChild(opt);
                }

                // Show recommended action hint and badge without auto-selecting it in dropdown
                if (app.suggested_action) {
                    if (badge) {
                        badge.classList.remove('hidden');
                        badge.classList.add('inline-flex');
                    }

                    if (hint) {
                        const hintText = document.getElementById('suggestedActionText');
                        if (hintText) {
                            hintText.textContent = app.suggested_reason || 'Next step based on current routing custody.';
                        }
                        hint.classList.remove('hidden');
                    }
                }
            }
        }
    }

    function appendRecentScan(data, app) {
        if (!data || !data.log_entry) return;
        const list = document.getElementById('recentScansList');
        if (!list) return;

        // Remove empty state message if present
        const emptyMsg = list.querySelector('.p-8');
        if (emptyMsg) emptyMsg.remove();

        const item = document.createElement('div');
        item.onclick = () => fillReference(data.reference_code);
        item.className = 'p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition cursor-pointer group bg-emerald-50/40 border-b border-slate-100';
        item.title = `Click to load ${data.reference_code} in scanner`;
        const isApproved = data.log_entry.action && data.log_entry.action.includes('APPROVED');

        item.innerHTML = `
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-gov-800 text-sm group-hover:underline">${data.reference_code}</span>
                    <span class="font-semibold text-slate-900 truncate">${app?.employee?.full_name || 'Municipal Employee'}</span>
                </div>
                <div class="text-xs text-slate-400 truncate mt-1">${data.current_location?.office_name || 'Station'} • Just now</div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold ${isApproved ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-800 border border-slate-200'}">
                    ${data.log_entry.action}
                </span>
                <div class="text-xs text-slate-400 font-mono mt-1">Just now</div>
            </div>
        `;
        list.prepend(item);
    }

    async function handleFormSubmit(e) {
        if (e) e.preventDefault();
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn && submitBtn.disabled) {
            return;
        }

        const code = document.getElementById('reference_code').value.trim();
        const resolvedId = document.getElementById('resolved_application_id').value;
        const action = document.getElementById('action_select')?.value;
        const officeId = document.getElementById('office_id').value;
        const remarks = document.getElementById('remarks')?.value;

        if (!code || !resolvedId || !currentLoadedApp) {
            showToast('Please look up and review the document preview first before updating the routing log.', 'warning');
            if (code) {
                lookupReference();
            }
            return;
        }

        const appId = resolvedId;

        if (!action) {
            showToast('Please select a routing action from the dropdown before updating the log.', 'warning');
            const actionSelect = document.getElementById('action_select');
            if (actionSelect) actionSelect.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph ph-spinner animate-spin text-xl"></i> Updating...';

        const clerkUserId = document.getElementById('clerk_user_id')?.value;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(`/api/routing/${appId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    action: action,
                    office_id: officeId,
                    user_id: clerkUserId,
                    method: 'QR_SCAN',
                    remarks: remarks,
                }),
            });

            const data = await res.json();
            if (data.success) {
                // Success Audio Chime feedback (synthesized web audio)
                playSuccessChime();

                appendRecentScan(data.data, currentLoadedApp);

                if (data.workflow_state && (!data.workflow_state.can_act || data.is_completed)) {
                    showToast(`${data.message} All needed routing actions are now finished.`, 'success');
                } else {
                    showToast(data.message, 'success');
                }

                // Immediately re-fetch to advance state and show completion card if finished
                lookupReference();
            } else {
                showToast(`Routing Restriction: ${data.message}`, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ph ph-paper-plane-right text-xl"></i> <span>Update Routing Log</span>';
            }
        } catch (err) {
            console.error(err);
            showToast('Failed to update routing status. Please check connection.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ph ph-paper-plane-right text-xl"></i> <span>Update Routing Log</span>';
        }
    }

    async function certifySection7A(appId) {
        const btn = document.getElementById(`certifyBtn_${appId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin text-base"></i> Certifying...';
        }

        const officeId = document.getElementById('office_id').value;
        const clerkUserId = document.getElementById('clerk_user_id')?.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const res = await fetch(`/api/routing/${appId}/certify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    office_id: officeId,
                    user_id: clerkUserId,
                }),
            });

            const data = await res.json();
            if (data.success) {
                playSuccessChime();
                showToast(data.message, 'success');
                // Refresh preview card
                lookupReference();
            } else {
                showToast(`Certification Notice: ${data.message}`, 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-stamp text-base"></i> <span>Certify Section 7.A Now</span>';
                }
            }
        } catch (e) {
            console.error(e);
            showToast('Failed to certify leave credits. Please verify connection.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-stamp text-base"></i> <span>Certify Section 7.A Now</span>';
            }
        }
    }

    function playSuccessChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.setValueAtTime(880.00, ctx.currentTime + 0.1); // A5
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch (e) {
            // Audio context not allowed or unsupported
        }
    }

    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

</script>
@endsection


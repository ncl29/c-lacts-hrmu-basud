# ======================================================================
# C-LACTS Online Server Launcher - Municipality of Basud
# Exposes C-LACTS to any device and any network online via Cloudflare Tunnel
# ======================================================================

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host " C-LACTS : Centralized Leave Application & Credit Tracking System" -ForegroundColor White
Write-Host " Municipality of Basud, Camarines Norte - HRMU" -ForegroundColor Yellow
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""

$phpPath = "C:\xampp\php\php.exe"
if (-not (Test-Path $phpPath)) { $phpPath = "php" }

$cloudflaredPath = "C:\Program Files (x86)\cloudflared\cloudflared.exe"
if (-not (Test-Path $cloudflaredPath)) { $cloudflaredPath = "cloudflared" }

Write-Host "[1/2] Starting local Laravel backend on port 8000..." -ForegroundColor Green
$serverProc = Start-Process -FilePath $phpPath -ArgumentList "artisan serve --host=0.0.0.0 --port=8000" -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 2

Write-Host "[2/2] Launching secure Cloudflare Online Tunnel..." -ForegroundColor Green
Write-Host ""
Write-Host "----------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " ONLINE ACCESS INSTRUCTIONS:" -ForegroundColor Yellow
Write-Host " 1. Look for the public link below (e.g. https://...trycloudflare.com)" -ForegroundColor White
Write-Host " 2. Open that link on your smartphone, tablet, or external PC." -ForegroundColor White
Write-Host " 3. Works on cellular data (4G/5G) and any Wi-Fi network worldwide." -ForegroundColor White
Write-Host " 4. Camera QR scanning for CS Form 6 is fully enabled via HTTPS." -ForegroundColor White
Write-Host ""
Write-Host " (Press Ctrl+C anytime to stop the online tunnel)" -ForegroundColor DarkGray
Write-Host "----------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host ""

try {
    & $cloudflaredPath tunnel --url http://localhost:8000
} finally {
    Write-Host ""
    Write-Host "Stopping Laravel backend server..." -ForegroundColor Yellow
    if ($serverProc -and -not $serverProc.HasExited) {
        Stop-Process -Id $serverProc.Id -Force -ErrorAction SilentlyContinue
    }
    Write-Host "C-LACTS server stopped." -ForegroundColor Green
}

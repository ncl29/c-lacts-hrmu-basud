@echo off
title C-LACTS Online Server - Municipality of Basud
color 0B
echo ======================================================================
echo  C-LACTS : Centralized Leave Application ^& Credit Tracking System
echo  Municipality of Basud, Camarines Norte - HRMU
echo ======================================================================
echo.
echo [1/2] Starting local Laravel backend on port 8000...
set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php

start /B "" "%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000 > nul 2>&1
timeout /t 2 /nobreak > nul

echo [2/2] Launching secure Cloudflare Online Tunnel...
echo.
echo ======================================================================
echo  ONLINE ACCESS INSTRUCTIONS:
echo  1. Look for the public HTTPS link below (e.g. https://...trycloudflare.com)
echo  2. Open that link on your smartphone, tablet, or any remote PC.
echo  3. Works on cellular data (4G/5G) and any Wi-Fi network worldwide!
echo  4. Camera QR scanning for CS Form 6 is fully enabled via HTTPS.
echo.
echo  (Press Ctrl+C anytime to stop the online tunnel)
echo ======================================================================
echo.

set CLOUDFLARED="C:\Program Files (x86)\cloudflared\cloudflared.exe"
if not exist %CLOUDFLARED% set CLOUDFLARED=cloudflared

%CLOUDFLARED% tunnel --url http://localhost:8000

echo.
echo Stopping Laravel backend...
taskkill /F /IM php.exe /FI "WINDOWTITLE eq C-LACTS*" > nul 2>&1
echo Done.
pause

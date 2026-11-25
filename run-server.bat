@echo off
title C-LACTS HRMU Basud - Server
echo ====================================================================
echo   C-LACTS HRMU Basud Development Server
echo   Opening on: http://localhost:8080
echo ====================================================================
echo.
where php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    php artisan serve --port=8080
) else (
    "C:\xampp\php\php.exe" artisan serve --port=8080
)
pause

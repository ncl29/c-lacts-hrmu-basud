@echo off
title Stop C-LACTS Online Server
echo Stopping all C-LACTS server and tunnel processes...
taskkill /F /IM cloudflared.exe > nul 2>&1
echo Cloudflare tunnel stopped.
echo Done.
timeout /t 2 > nul

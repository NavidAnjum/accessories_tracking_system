@echo off
rem  Protocol launcher for atsmail://  — runs the PowerShell helper hidden.
rem  %1 is the full "atsmail://open?..." string passed by Windows.
powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%~dp0AtsMailHelper.ps1" -Uri "%~1"

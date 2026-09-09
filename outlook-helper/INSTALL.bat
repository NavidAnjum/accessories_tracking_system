@echo off
setlocal
title ATS "Email PI" — one-click installer

rem ─────────────────────────────────────────────────────────────────────────
rem  One-click installer for the ATS "Email PI (Outlook)" helper.
rem  Run this ONCE on each PC that needs the button. No admin rights required
rem  (installs for the current Windows user only).
rem
rem  What it does:
rem    1. Copies AtsMailHelper.ps1 + ats-mail-launch.cmd to  %LOCALAPPDATA%\ATSMail
rem    2. Registers the  atsmail://  protocol for the current user
rem  ─────────────────────────────────────────────────────────────────────────

set "SRC=%~dp0"
set "DEST=%LOCALAPPDATA%\ATSMail"

echo.
echo   Installing ATS "Email PI" helper...
echo   Target folder: %DEST%
echo.

rem 1. Copy the helper files
if not exist "%DEST%" mkdir "%DEST%"
copy /Y "%SRC%AtsMailHelper.ps1"  "%DEST%\AtsMailHelper.ps1"  >nul
copy /Y "%SRC%ats-mail-launch.cmd" "%DEST%\ats-mail-launch.cmd" >nul

if not exist "%DEST%\AtsMailHelper.ps1" (
    echo   ERROR: could not copy helper files. Make sure this .bat is in the same
    echo   folder as AtsMailHelper.ps1 and ats-mail-launch.cmd, then run again.
    echo.
    pause
    exit /b 1
)

rem 2. Register the atsmail:// protocol for HKEY_CURRENT_USER (no admin needed)
set "LAUNCH=%DEST%\ats-mail-launch.cmd"
set "CMD=cmd.exe /c \"\"%LAUNCH%\" \"%%1\"\""

reg add "HKCU\Software\Classes\atsmail" /ve /d "URL:ATS Email PI Protocol" /f >nul
reg add "HKCU\Software\Classes\atsmail" /v "URL Protocol" /d "" /f >nul
reg add "HKCU\Software\Classes\atsmail\shell\open\command" /ve /d "%CMD%" /f >nul

if errorlevel 1 (
    echo   ERROR: could not register the atsmail:// protocol.
    echo.
    pause
    exit /b 1
)

echo   Done! The "Email PI (Outlook)" button is ready on this PC.
echo.
echo   Tips:
echo     - Keep your ATS login active before using the Email PI button.
echo     - On the first click, tick "Always allow" in the browser prompt.
echo.
pause
endlocal

@echo off
setlocal
title ATS "Email PI" — uninstaller

echo.
echo   Removing ATS "Email PI" helper...
reg delete "HKCU\Software\Classes\atsmail" /f >nul 2>&1
rmdir /S /Q "%LOCALAPPDATA%\ATSMail" >nul 2>&1
echo   Done.
echo.
pause
endlocal

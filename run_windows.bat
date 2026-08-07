@echo off
setlocal EnableDelayedExpansion
rem ============================================================
rem  CV Amirul Putra Justicia - Windows Launcher
rem  Menjalankan PHP Laragon dari sisi Windows ke folder WSL,
rem  sehingga koneksi ke PostgreSQL Windows (localhost:5432) jalan.
rem ============================================================

set ROOT=\\wsl.localhost\Ubuntu\home\amirulputraj\cvamirulputrajusticia
set PHP=

if exist "C:\laragon\bin\php\php-8.4.16-Win32-vs17-x64\php.exe" set PHP=C:\laragon\bin\php\php-8.4.16-Win32-vs17-x64\php.exe
if not defined PHP if exist "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" set PHP=C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe

if not defined PHP (
    echo.
    echo [ERROR] PHP Laragon tidak ditemukan. Install dulu dari https://laragon.org
    echo.
    pause
    exit /b 1
)

if not exist "%ROOT%" (
    echo.
    echo [ERROR] Folder WSL tidak ditemukan: %ROOT%
    echo Pastikan distro WSL 'Ubuntu' sudah berjalan.
    echo.
    pause
    exit /b 1
)

set PORT=8000
:loop
netstat -an | findstr /r /c:":%PORT% " >nul 2>&1
if errorlevel 1 goto found
set /a PORT+=1
goto loop
:found

echo.
echo  CV App  -^>  http://localhost:%PORT%
echo  Tekan Ctrl+C untuk stop.
echo.
start "" "http://localhost:%PORT%"
"%PHP%" -S 127.0.0.1:%PORT% -t "%ROOT%"

@echo off
setlocal EnableExtensions EnableDelayedExpansion
rem ------------------------------------------------------------------
rem LeatherX local setup for Windows (XAMPP MySQL + PHP 8.3).
rem Double-click this file, or run it from any terminal.
rem It finds a PHP 8.3+ install, enables the extensions Laravel needs,
rem then installs dependencies, migrates + seeds and starts the server.
rem ------------------------------------------------------------------
cd /d "%~dp0.."

echo.
echo === LeatherX local setup ===
echo.

rem 1. Find PHP 8.3 or newer ----------------------------------------
set "PHPDIR="
for %%D in ("C:\php83" "C:\php" "C:\xampp\php") do (
    if not defined PHPDIR if exist "%%~D\php.exe" call :checkphp "%%~D"
)
if not defined PHPDIR for /d %%D in ("C:\php-8.*" "%USERPROFILE%\Downloads\php-8.*" "%USERPROFILE%\Desktop\php-8.*") do (
    if not defined PHPDIR if exist "%%~D\php.exe" call :checkphp "%%~D"
)
if not defined PHPDIR (
    echo [ERROR] PHP 8.3 or newer was not found.
    echo.
    echo  1. Download "PHP 8.3 - VS16 x64 Thread Safe" Zip from https://windows.php.net/download/
    echo  2. Right-click the zip ^> Extract All ^> extract to C:\php83
    echo     ^(C:\php83\php.exe must exist^)
    echo  3. Run this file again.
    goto :fail
)
set "PATH=%PHPDIR%;%PATH%"
echo [OK] Using PHP from %PHPDIR%
"%PHPDIR%\php.exe" -r "echo '     PHP ', PHP_VERSION, PHP_EOL;"

rem 2. php.ini + required extensions ---------------------------------
set "INI=%PHPDIR%\php.ini"
if not exist "%INI%" (
    if exist "%PHPDIR%\php.ini-development" (
        copy /y "%PHPDIR%\php.ini-development" "%INI%" >nul
    ) else (
        type nul > "%INI%"
    )
    echo [OK] Created php.ini
)
set "ADDED="
for %%E in (openssl curl fileinfo gd intl mbstring pdo_mysql zip sodium exif) do (
    "%PHPDIR%\php.exe" -r "exit(extension_loaded('%%E') ? 0 : 1);" >nul 2>&1
    if errorlevel 1 (
        if not defined ADDED (
            >>"%INI%" echo.
            >>"%INI%" echo ; Added by LeatherX setup
            >>"%INI%" echo extension_dir="%PHPDIR%\ext"
            >>"%INI%" echo memory_limit=512M
            set "ADDED=1"
        )
        >>"%INI%" echo extension=%%E
        echo [OK] Enabled extension %%E
    )
)
for %%E in (openssl pdo_mysql mbstring intl fileinfo) do (
    "%PHPDIR%\php.exe" -r "exit(extension_loaded('%%E') ? 0 : 1);" >nul 2>&1
    if errorlevel 1 (
        echo [ERROR] PHP extension %%E could not be loaded. Check %INI%
        goto :fail
    )
)

rem 3. Composer --------------------------------------------------------
where composer >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer not found. Install it from https://getcomposer.org/Composer-Setup.exe
    echo         and choose %PHPDIR%\php.exe when the installer asks for PHP.
    goto :fail
)
echo.
echo === composer install ===
call composer install --no-interaction
if errorlevel 1 goto :fail

rem 4. .env --------------------------------------------------------------
if not exist ".env" (
    copy /y ".env.example" ".env" >nul
    echo [OK] Created .env from .env.example
)
findstr /r /c:"^APP_KEY=base64" ".env" >nul || php artisan key:generate --ansi
if errorlevel 1 goto :fail

rem 5. Frontend build (only if missing) --------------------------------
if not exist "public\build\manifest.json" (
    where npm >nul 2>&1 && (
        echo.
        echo === npm install + build ===
        call npm install
        call npm run build
    )
)

rem 6. Database ---------------------------------------------------------
echo.
echo Make sure MySQL is STARTED in the XAMPP Control Panel.
pause
php artisan optimize:clear
echo.
echo === Migrating + seeding (answer "yes" if asked to create the database) ===
php artisan migrate --seed
if errorlevel 1 goto :fail
if not exist "public\storage" php artisan storage:link

echo.
echo ==============================================================
echo  Done. Website: http://localhost:8000
echo        Admin:   http://localhost:8000/admin
echo  Admin login = the email/password printed above by the seeder
echo  ^(or ADMIN_EMAIL / ADMIN_PASSWORD in .env^).
echo  Press Ctrl+C to stop the server.
echo ==============================================================
echo.
php artisan serve
goto :eof

:checkphp
"%~1\php.exe" -r "exit(PHP_VERSION_ID >= 80300 ? 0 : 1);" >nul 2>&1
if not errorlevel 1 set "PHPDIR=%~1"
exit /b 0

:fail
echo.
echo Setup stopped. Fix the error above and run this file again.
pause
exit /b 1

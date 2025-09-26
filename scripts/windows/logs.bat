@echo off
setlocal enabledelayedexpansion

:: GearFalcon Logs and Monitoring Script
:: =====================================

if "%1"=="dev" (
    echo 📋 GearFalcon Development Logs
    echo =============================
    echo.
    echo Available services:
    echo   • frontend - Next.js frontend
    echo   • backend  - PHP backend
    echo   • db       - MySQL database
    echo.
    echo Usage: logs.bat dev [service-name]
    echo.
    if "%2"=="" (
        echo Showing all development logs...
        echo Press Ctrl+C to stop following logs
        echo.
        docker-compose -f docker-compose.windows.yml logs -f
    ) else (
        echo Showing logs for service: %2
        echo Press Ctrl+C to stop following logs
        echo.
        docker-compose -f docker-compose.windows.yml logs -f %2
    )
) else if "%1"=="prod" (
    echo 📋 GearFalcon Production Logs
    echo =============================
    echo.
    echo Available services:
    echo   • frontend - Next.js frontend
    echo   • backend  - PHP backend
    echo   • db       - MySQL database
    echo.
    echo Usage: logs.bat prod [service-name]
    echo.
    if "%2"=="" (
        echo Showing all production logs...
        echo Press Ctrl+C to stop following logs
        echo.
        docker-compose -f docker-compose.prod.yml logs -f
    ) else (
        echo Showing logs for service: %2
        echo Press Ctrl+C to stop following logs
        echo.
        docker-compose -f docker-compose.prod.yml logs -f %2
    )
) else if "%1"=="status" (
    echo 📊 GearFalcon Service Status
    echo ============================
    echo.
    echo Development Environment:
    docker-compose -f docker-compose.windows.yml ps
    echo.
    echo Production Environment:
    docker-compose -f docker-compose.windows.yml ps
    echo.
    echo Resource Usage:
    docker stats --no-stream
) else if "%1"=="health" (
    echo 🔍 GearFalcon Health Check
    echo ==========================
    echo.

    REM Create temporary file for response validation
    set "TEMP_FILE=%TEMP%\health_check_%RANDOM%.json"

    echo Checking Development Services:
    call :check_health "http://localhost:3000/api/health" "Frontend"
    call :check_health "http://localhost:8080/health" "Backend"
    echo.
    echo Checking Production Services:
    call :check_health "http://localhost:3000/api/health" "Frontend"
    call :check_health "http://localhost:8080/health" "Backend"

    REM Clean up temporary file
    if exist "%TEMP_FILE%" del "%TEMP_FILE%" 2>nul

    goto :eof

:check_health
    set "HEALTH_URL=%~1"
    set "SERVICE_NAME=%~2"

    echo   %SERVICE_NAME%: %HEALTH_URL%

    REM Use curl with proper headers, timeout, and save response
    curl -f -s --max-time 10 --header "Accept: application/json" "%HEALTH_URL%" > "%TEMP_FILE%" 2>nul

    if %errorlevel% neq 0 (
        echo ❌ %SERVICE_NAME% FAILED - No response or timeout
        goto :health_error
    )

    REM Check if response contains expected JSON structure
    findstr /C:"\"status\"" "%TEMP_FILE%" >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ %SERVICE_NAME% FAILED - Invalid response format
        goto :health_error
    )

    echo ✅ %SERVICE_NAME% OK
    goto :eof

:health_error
    echo ❌ %SERVICE_NAME% FAILED
    if exist "%TEMP_FILE%" (
        echo Response received:
        type "%TEMP_FILE%"
    )
    echo.
    goto :eof
) else if "%1"=="cleanup" (
    echo 🧹 GearFalcon Cleanup
    echo ====================
    echo.
    echo This will remove:
    echo   • Stopped containers
    echo   • Unused networks
    echo   • Unused volumes
    echo   • Build cache
    echo.
    echo ⚠️  This will NOT remove running containers!
    echo.
    set /p "confirm=Are you sure you want to cleanup? (y/N): "
    if /i "!confirm!"=="y" (
        echo.
        echo Stopping all services first...
        docker-compose -f docker-compose.windows.yml down --timeout 30 2>nul

        echo.
        echo Cleaning up Docker resources...
        docker system prune -f
        docker volume prune -f
        docker network prune -f

        echo.
        echo ✅ Cleanup completed!
        echo.
        echo 📊 System Status:
        docker system df
    ) else (
        echo.
        echo ❌ Cleanup cancelled.
    )
) else (
    echo 📋 GearFalcon Logs and Monitoring
    echo ================================
    echo.
    echo Usage:
    echo   scripts\windows\logs.bat dev [service]     - View development logs
    echo   scripts\windows\logs.bat prod [service]    - View production logs
    echo   logs.bat status            - Show service status
    echo   logs.bat health            - Check service health
    echo   logs.bat cleanup           - Clean up Docker resources
    echo.
    echo Examples:
    echo   scripts\windows\logs.bat dev               - All development logs
    echo   scripts\windows\logs.bat dev backend       - Backend development logs
    echo   scripts\windows\logs.bat prod frontend     - Frontend production logs
    echo   scripts\windows\logs.bat status            - Show all service status
    echo   scripts\windows\logs.bat health            - Check all service health
    echo.
    echo Press any key to exit...
    pause >nul
)
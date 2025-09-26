@echo off
setlocal enabledelayedexpansion

:: GearFalcon Development Startup Script
:: =====================================

echo Starting GearFalcon in Development Mode...
echo =========================================
echo.

:: Check if Docker is installed and running
echo [1/5] Checking Docker installation...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not installed or not running!
    echo Please install Docker Desktop and try again.
    pause
    exit /b 1
)
echo ✅ Docker is available

:: Check if docker-compose is available
echo [2/5] Checking Docker Compose...
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose is not available!
    echo Please ensure Docker Compose is installed.
    pause
    exit /b 1
)
echo ✅ Docker Compose is available

:: Navigate to project root directory (parent of scripts directory)
echo [3/5] Setting up environment...
cd /d "%~dp0\..\.."
if errorlevel 1 (
    echo ❌ Failed to change directory!
    pause
    exit /b 1
)

:: Check if docker-compose.yml exists
if not exist "docker-compose.yml" (
    echo ❌ docker-compose.yml not found!
    echo Please ensure you're in the correct directory.
    pause
    exit /b 1
)
echo ✅ Environment ready

:: Display service information
echo.
echo 🌐 Service URLs:
echo    Frontend: http://localhost:3000
echo    Backend:  http://localhost:8080
echo    Health Check: http://localhost:8080/health
echo.
echo 📊 Monitoring Commands:
echo    View logs:      docker-compose -f docker-compose.windows.yml logs -f
echo    Check status:   docker-compose -f docker-compose.windows.yml ps
echo    Stop services:  Ctrl+C or double-click scripts\windows\stop.bat
echo.

:: Start services with build
echo [4/5] Building and starting services...
echo (This may take several minutes on first run)
echo.

docker-compose -f docker-compose.windows.yml up --build

:: Check if services started successfully
echo.
echo [5/5] Verifying services...
timeout /t 10 /nobreak >nul

:: Check if containers are running
docker-compose -f docker-compose.windows.yml ps --quiet | findstr . >nul 2>&1
if errorlevel 1 (
    echo ❌ Services failed to start properly!
    echo Check the logs above for errors.
    pause
    exit /b 1
)

echo.
echo ✅ GearFalcon Development Environment Started Successfully!
echo.
echo 🎉 Available Services:
echo    • Frontend (Next.js)  - http://localhost:3000
echo    • Backend (PHP)      - http://localhost:8080
echo    • Database (MySQL)   - Internal only
echo.
echo 📋 Useful Commands:
echo    docker-compose -f docker-compose.windows.yml logs -f     - View all logs
echo    docker-compose -f docker-compose.windows.yml logs [service] - View specific service logs
echo    docker-compose -f docker-compose.windows.yml down        - Stop all services
echo    docker-compose -f docker-compose.windows.yml restart     - Restart all services
echo.
echo Press Ctrl+C to stop all services...
echo.

:: Keep window open for monitoring
pause >nul
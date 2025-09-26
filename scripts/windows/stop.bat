@echo off
setlocal enabledelayedexpansion

:: GearFalcon Service Stop Script
:: ==============================

echo Stopping all GearFalcon services...
echo ===================================
echo.

:: Check if Docker is available
echo [1/4] Checking Docker availability...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not available!
    echo Please start Docker Desktop first.
    pause
    exit /b 1
)
echo ✅ Docker is available

:: Navigate to script directory
echo [2/4] Setting up environment...
cd /d "%~dp0"
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

:: Stop services with timeout and cleanup
echo [3/4] Stopping services gracefully...
echo (This may take up to 30 seconds)
echo.

:: First, try graceful shutdown
docker-compose down --timeout 30 2>nul

:: Check for orphaned containers and clean them up
echo [4/4] Cleaning up orphaned containers...
for /f "tokens=*" %%i in ('docker-compose ps --quiet 2^>nul') do (
    set "container_found=1"
    docker stop %%i --time 10 >nul 2>&1
    docker rm %%i >nul 2>&1
)

:: Clean up unused networks
for /f "tokens=*" %%i in ('docker network ls --filter "label=com.docker.compose.project=gearfalcon-app" --quiet 2^>nul') do (
    docker network rm %%i >nul 2>&1
)

:: Verify all services are stopped
timeout /t 3 /nobreak >nul
docker-compose ps --quiet 2>nul | findstr . >nul 2>&1
if not errorlevel 1 (
    echo ⚠️  Some services may still be running.
    echo Forcing cleanup...
    docker-compose down --volumes --remove-orphans --timeout 5 2>nul
)

echo.
echo ✅ All services stopped successfully!
echo.

:: Show system status
echo 📊 System Status:
docker-compose ps 2>nul | findstr /v "Name\|---" | findstr . >nul 2>&1
if errorlevel 1 (
    echo    • No containers running
) else (
    echo    • Some containers still active
)

:: Show disk usage
echo.
echo 💾 Docker Disk Usage:
for /f "tokens=*" %%i in ('docker system df --format "table {{.Type}}\t{{.TotalCount}}\t{{.Size}}" 2^>nul') do (
    echo    %%i
)

echo.
echo 🚀 To start services again:
echo    • Development: double-click start-dev.bat
echo    • Production:  double-click start-prod.bat
echo.
echo 🛠️  Maintenance Commands:
echo    docker-compose logs -f          - View all logs
echo    docker system prune             - Clean up unused resources
echo    docker volume prune             - Remove unused volumes
echo.

echo Press any key to exit...
pause >nul
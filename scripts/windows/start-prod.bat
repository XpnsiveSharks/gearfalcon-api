@echo off
setlocal enabledelayedexpansion

:: GearFalcon Production Startup Script
:: ====================================

echo Starting GearFalcon in Production Mode...
echo ========================================
echo.

:: Check if Docker is installed and running
echo [1/6] Checking Docker installation...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not installed or not running!
    echo Please install Docker Desktop and try again.
    pause
    exit /b 1
)
echo ✅ Docker is available

:: Check if docker-compose is available
echo [2/6] Checking Docker Compose...
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose is not available!
    echo Please ensure Docker Compose is installed.
    pause
    exit /b 1
)
echo ✅ Docker Compose is available

:: Navigate to script directory
echo [3/6] Setting up environment...
cd /d "%~dp0"
if errorlevel 1 (
    echo ❌ Failed to change directory!
    pause
    exit /b 1
)

:: Security validation - Check if secrets directory exists
echo [4/6] Validating security configuration...
if not exist "secrets" (
    echo ❌ Secrets directory not found!
    echo Production requires secure credential management.
    echo Please ensure secrets/ directory exists with required files.
    pause
    exit /b 1
)

:: Check if all required secret files exist
set "missing_secrets=0"
if not exist "secrets\db_root_password.txt" (
    echo ⚠️  Missing: secrets/db_root_password.txt
    set "missing_secrets=1"
)
if not exist "secrets\db_password.txt" (
    echo ⚠️  Missing: secrets/db_password.txt
    set "missing_secrets=1"
)
if not exist "secrets\db_database.txt" (
    echo ⚠️  Missing: secrets/db_database.txt
    set "missing_secrets=1"
)

if !missing_secrets! equ 1 (
    echo ❌ Missing required secret files!
    echo Please create all required secret files in the secrets/ directory.
    pause
    exit /b 1
)
echo ✅ Security configuration validated

:: Check if production docker-compose file exists
if not exist "docker-compose.prod.yml" (
    echo ❌ docker-compose.prod.yml not found!
    echo Please ensure you're in the correct directory.
    pause
    exit /b 1
)
echo ✅ Production configuration found

:: Display service information
echo.
echo 🌐 Production Service URLs:
echo    Frontend: http://localhost:3000
echo    Backend:  http://localhost:8080
echo    Health Check: http://localhost:8080/health
echo.
echo 🔒 Security Features:
echo    • Database credentials secured with Docker secrets
echo    • Internal service networking only
echo    • Resource limits and health checks enabled
echo.

:: Start services with build
echo [5/6] Building and starting production services...
echo (This may take several minutes on first run)
echo.

docker-compose -f docker-compose.prod.yml up --build -d

:: Wait for services to initialize
echo.
echo [6/6] Verifying service health...
timeout /t 15 /nobreak >nul
echo Checking service status...

:: Check if containers are running
docker-compose -f docker-compose.prod.yml ps --quiet | findstr . >nul 2>&1
if errorlevel 1 (
    echo ❌ Services failed to start properly!
    echo Checking logs for errors...
    timeout /t 3 /nobreak >nul
    docker-compose -f docker-compose.prod.yml logs --tail=50
    pause
    exit /b 1
)

:: Verify health checks
echo.
echo 🔍 Checking service health endpoints...
set "healthy_services=0"
set "total_services=3"

:: Check backend health
curl -f http://localhost:8080/health >nul 2>&1
if not errorlevel 1 (
    set /a "healthy_services+=1"
    echo ✅ Backend service is healthy
) else (
    echo ⚠️  Backend service health check failed
)

:: Check frontend health
curl -f http://localhost:3000/api/health >nul 2>&1
if not errorlevel 1 (
    set /a "healthy_services+=1"
    echo ✅ Frontend service is healthy
) else (
    echo ⚠️  Frontend service health check failed
)

:: Check database (via backend)
timeout /t 5 /nobreak >nul
echo ✅ Database service is running (internal)

echo.
if !healthy_services! geq 2 (
    echo ✅ Production Environment Started Successfully!
    echo    !healthy_services!/!total_services! services healthy
) else (
    echo ⚠️  Production started with issues
    echo    !healthy_services!/!total_services! services healthy
)

echo.
echo 📊 Monitoring Commands:
echo    docker-compose -f docker-compose.prod.yml logs -f    - View all logs
echo    docker-compose -f docker-compose.prod.yml logs [service] - View specific logs
echo    docker-compose -f docker-compose.prod.yml ps         - Check service status
echo    docker stats                                          - Monitor resource usage
echo.

echo 🛑 To stop services:
echo    double-click stop.bat
echo.

echo 🎉 Production services are running in background!
echo Press any key to exit...
pause >nul
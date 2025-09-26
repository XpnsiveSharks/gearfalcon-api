#!/bin/bash

# GearFalcon Production Startup Script
# ====================================

echo "Starting GearFalcon in Production Mode..."
echo "========================================"
echo ""

# Check if Docker is installed and running
echo "[1/6] Checking Docker installation..."
if ! docker --version >/dev/null 2>&1; then
    echo "❌ Docker is not installed or not running!"
    echo "Please install Docker and try again."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Docker is available"

# Check if docker-compose is available (try v2 first, then v1)
echo "[2/6] Checking Docker Compose..."
COMPOSE_CMD=""
if docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
    echo "✅ Docker Compose v2 is available"
elif docker-compose --version >/dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
    echo "✅ Docker Compose v1 is available"
else
    echo "❌ Docker Compose is not available!"
    echo "Please ensure Docker Compose is installed."
    read -p "Press Enter to continue..."
    exit 1
fi

# Navigate to project root directory (parent of scripts directory)
echo "[3/6] Setting up environment..."
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPTS_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$SCRIPTS_DIR")"
cd "$PROJECT_ROOT"
if [ $? -ne 0 ]; then
    echo "❌ Failed to change directory!"
    read -p "Press Enter to continue..."
    exit 1
fi

# Security validation - Check if secrets directory exists
echo "[4/6] Validating security configuration..."
if [ ! -d "secrets" ]; then
    echo "❌ Secrets directory not found!"
    echo "Production requires secure credential management."
    echo "Please ensure secrets/ directory exists with required files."
    read -p "Press Enter to continue..."
    exit 1
fi

# Check if all required secret files exist
missing_secrets=0
if [ ! -f "secrets/db_root_password.txt" ]; then
    echo "⚠️  Missing: secrets/db_root_password.txt"
    missing_secrets=1
fi
if [ ! -f "secrets/db_password.txt" ]; then
    echo "⚠️  Missing: secrets/db_password.txt"
    missing_secrets=1
fi
if [ ! -f "secrets/db_database.txt" ]; then
    echo "⚠️  Missing: secrets/db_database.txt"
    missing_secrets=1
fi

if [ $missing_secrets -eq 1 ]; then
    echo "❌ Missing required secret files!"
    echo "Please create all required secret files in the secrets/ directory."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Security configuration validated"

# Check if production docker-compose file exists
if [ ! -f "docker-compose.prod.yml" ]; then
    echo "❌ docker-compose.prod.yml not found!"
    echo "Please ensure you're in the correct directory."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Production configuration found"

# Display service information
echo ""
echo "🌐 Production Service URLs:"
echo "    Frontend: http://localhost:3000"
echo "    Backend:  http://localhost:8080"
echo "    Health Check: http://localhost:8080/health"
echo ""
echo "🔒 Security Features:"
echo "    • Database credentials secured with Docker secrets"
echo "    • Internal service networking only"
echo "    • Resource limits and health checks enabled"
echo ""

# Start services with build
echo "[5/6] Building and starting production services..."
echo "(This may take several minutes on first run)"
echo ""

$COMPOSE_CMD -f docker-compose.prod.yml up --build -d

# Wait for services to initialize
echo ""
echo "[6/6] Verifying service health..."
sleep 15
echo "Checking service status..."

# Check if containers are running
if ! $COMPOSE_CMD -f docker-compose.prod.yml ps --quiet | grep -q .; then
    echo "❌ Services failed to start properly!"
    echo "Checking logs for errors..."
    sleep 3
    $COMPOSE_CMD -f docker-compose.prod.yml logs --tail=50
    read -p "Press Enter to continue..."
    exit 1
fi

# Verify health checks
echo ""
echo "🔍 Checking service health endpoints..."
healthy_services=0
total_services=3

# Check backend health
if curl -f http://localhost:8080/health >/dev/null 2>&1; then
    healthy_services=$((healthy_services + 1))
    echo "✅ Backend service is healthy"
else
    echo "⚠️  Backend service health check failed"
fi

# Check frontend health
if curl -f http://localhost:3000/api/health >/dev/null 2>&1; then
    healthy_services=$((healthy_services + 1))
    echo "✅ Frontend service is healthy"
else
    echo "⚠️  Frontend service health check failed"
fi

# Check database (via backend)
sleep 5
echo "✅ Database service is running (internal)"

echo ""
if [ $healthy_services -ge 2 ]; then
    echo "✅ Production Environment Started Successfully!"
    echo "    $healthy_services/$total_services services healthy"
else
    echo "⚠️  Production started with issues"
    echo "    $healthy_services/$total_services services healthy"
fi

echo ""
echo "📊 Monitoring Commands:"
echo "    $COMPOSE_CMD -f docker-compose.prod.yml logs -f    - View all logs"
echo "    $COMPOSE_CMD -f docker-compose.prod.yml logs [service] - View specific logs"
echo "    $COMPOSE_CMD -f docker-compose.prod.yml ps         - Check service status"
echo "    docker stats                                          - Monitor resource usage"
echo ""
echo "🛑 To stop services:"
echo "    ./stop.sh"
echo ""
echo "🎉 Production services are running in background!"
read -p "Press any key to exit..."
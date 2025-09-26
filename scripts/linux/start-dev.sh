#!/bin/bash

# GearFalcon Development Startup Script
# =====================================

echo "Starting GearFalcon in Development Mode..."
echo "==========================================="
echo ""

# Check if Docker is installed and running
echo "[1/5] Checking Docker installation..."
if ! docker --version >/dev/null 2>&1; then
    echo "❌ Docker is not installed or not running!"
    echo "Please install Docker and try again."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Docker is available"

# Check if docker-compose is available
echo "[2/5] Checking Docker Compose..."
if ! docker-compose --version >/dev/null 2>&1; then
    echo "❌ Docker Compose is not available!"
    echo "Please ensure Docker Compose is installed."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Docker Compose is available"

# Navigate to script directory
echo "[3/5] Setting up environment..."
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
if [ $? -ne 0 ]; then
    echo "❌ Failed to change directory!"
    read -p "Press Enter to continue..."
    exit 1
fi

# Check if docker-compose.yml exists
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ docker-compose.yml not found!"
    echo "Please ensure you're in the correct directory."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Environment ready"

# Display service information
echo ""
echo "🌐 Service URLs:"
echo "    Frontend: http://localhost:3000"
echo "    Backend:  http://localhost:8080"
echo "    Health Check: http://localhost:8080/health"
echo ""
echo "📊 Monitoring Commands:"
echo "    View logs:      docker-compose logs -f"
echo "    Check status:   docker-compose ps"
echo "    Stop services:  Ctrl+C or ./stop.sh"
echo ""

# Start services with build
echo "[4/5] Building and starting services..."
echo "(This may take several minutes on first run)"
echo ""

docker-compose up --build

# Check if services started successfully
echo ""
echo "[5/5] Verifying services..."
sleep 10

# Check if containers are running
if ! docker-compose ps --quiet | grep -q .; then
    echo "❌ Services failed to start properly!"
    echo "Check the logs above for errors."
    read -p "Press Enter to continue..."
    exit 1
fi

echo ""
echo "✅ GearFalcon Development Environment Started Successfully!"
echo ""
echo "🎉 Available Services:"
echo "    • Frontend (Next.js)  - http://localhost:3000"
echo "    • Backend (PHP)      - http://localhost:8080"
echo "    • Database (MySQL)   - Internal only"
echo ""
echo "📋 Useful Commands:"
echo "    docker-compose logs -f     - View all logs"
echo "    docker-compose logs [service] - View specific service logs"
echo "    docker-compose down        - Stop all services"
echo "    docker-compose restart     - Restart all services"
echo ""
echo "Press Ctrl+C to stop all services..."
echo ""

# Keep window open for monitoring
read -p "Press Enter to exit..."
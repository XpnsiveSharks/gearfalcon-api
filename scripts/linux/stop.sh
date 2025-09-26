#!/bin/bash

# GearFalcon Service Stop Script
# ==============================

echo "Stopping all GearFalcon services..."
echo "==================================="
echo ""

# Check if Docker is available
echo "[1/4] Checking Docker availability..."
if ! docker --version >/dev/null 2>&1; then
    echo "❌ Docker is not available!"
    echo "Please start Docker first."
    read -p "Press Enter to continue..."
    exit 1
fi
echo "✅ Docker is available"

# Navigate to script directory
echo "[2/4] Setting up environment..."
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

# Stop services with timeout and cleanup
echo "[3/4] Stopping services gracefully..."
echo "(This may take up to 30 seconds)"
echo ""

# First, try graceful shutdown
docker-compose down --timeout 30 2>/dev/null

# Check for orphaned containers and clean them up
echo "[4/4] Cleaning up orphaned containers..."
docker-compose ps --quiet 2>/dev/null | while read -r container; do
    if [ -n "$container" ]; then
        docker stop "$container" --time 10 >/dev/null 2>&1
        docker rm "$container" >/dev/null 2>&1
    fi
done

# Clean up unused networks
docker network ls --filter "label=com.docker.compose.project=gearfalcon-app" --quiet 2>/dev/null | while read -r net; do
    docker network rm "$net" >/dev/null 2>&1
done

# Verify all services are stopped
sleep 3
docker-compose ps --quiet 2>/dev/null | grep -q . && echo "⚠️  Some services may still be running." || echo "✅ All services stopped successfully!"

echo ""
echo "📊 System Status:"
if docker-compose ps --quiet 2>/dev/null | grep -q .; then
    echo "    • Some containers still active"
else
    echo "    • No containers running"
fi

# Show disk usage
echo ""
echo "💾 Docker Disk Usage:"
docker system df --format "table {{.Type}}\t{{.TotalCount}}\t{{.Size}}" 2>/dev/null | while read -r line; do
    echo "    $line"
done

echo ""
echo "🚀 To start services again:"
echo "    • Development: ./start-dev.sh"
echo "    • Production:  ./start-prod.sh"
echo ""
echo "🛠️  Maintenance Commands:"
echo "    docker-compose logs -f          - View all logs"
echo "    docker system prune             - Clean up unused resources"
echo "    docker volume prune             - Remove unused volumes"
echo ""

read -p "Press any key to exit..."
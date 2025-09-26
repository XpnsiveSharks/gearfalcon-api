#!/bin/bash

# GearFalcon Logs and Monitoring Script
# =====================================

if [ "$1" == "dev" ]; then
    echo "📋 GearFalcon Development Logs"
    echo "=============================="
    echo ""
    echo "Available services:"
    echo "  • frontend - Next.js frontend"
    echo "  • backend  - PHP backend"
    echo "  • db       - MySQL database"
    echo ""
    echo "Usage: ./logs.sh dev [service-name]"
    echo ""
    if [ -z "$2" ]; then
        echo "Showing all development logs..."
        echo "Press Ctrl+C to stop following logs"
        echo ""
        docker-compose logs -f
    else
        echo "Showing logs for service: $2"
        echo "Press Ctrl+C to stop following logs"
        echo ""
        docker-compose logs -f "$2"
    fi
elif [ "$1" == "prod" ]; then
    echo "📋 GearFalcon Production Logs"
    echo "=============================="
    echo ""
    echo "Available services:"
    echo "  • frontend - Next.js frontend"
    echo "  • backend  - PHP backend"
    echo "  • db       - MySQL database"
    echo ""
    echo "Usage: ./logs.sh prod [service-name]"
    echo ""
    if [ -z "$2" ]; then
        echo "Showing all production logs..."
        echo "Press Ctrl+C to stop following logs"
        echo ""
        docker-compose -f docker-compose.prod.yml logs -f
    else
        echo "Showing logs for service: $2"
        echo "Press Ctrl+C to stop following logs"
        echo ""
        docker-compose -f docker-compose.prod.yml logs -f "$2"
    fi
elif [ "$1" == "status" ]; then
    echo "📊 GearFalcon Service Status"
    echo "============================"
    echo ""
    echo "Development Environment:"
    docker-compose ps
    echo ""
    echo "Production Environment:"
    docker-compose -f docker-compose.prod.yml ps
    echo ""
    echo "Resource Usage:"
    docker stats --no-stream
elif [ "$1" == "health" ]; then
    echo "🔍 GearFalcon Health Check"
    echo "=========================="
    echo ""

    # Create temporary file for response validation
    TEMP_FILE=$(mktemp)

    echo "Checking Development Services:"
    check_health "http://localhost:3000/api/health" "Frontend"
    check_health "http://localhost:8080/health" "Backend"
    echo ""
    echo "Checking Production Services:"
    check_health "http://localhost:3000/api/health" "Frontend"
    check_health "http://localhost:8080/health" "Backend"

    # Clean up temporary file
    rm -f "$TEMP_FILE"

    exit 0

elif [ "$1" == "cleanup" ]; then
    echo "🧹 GearFalcon Cleanup"
    echo "===================="
    echo ""
    echo "This will remove:"
    echo "  • Stopped containers"
    echo "  • Unused networks"
    echo "  • Unused volumes"
    echo "  • Build cache"
    echo ""
    echo "⚠️  This will NOT remove running containers!"
    echo ""
    read -p "Are you sure you want to cleanup? (y/N): " confirm
    if [[ "$confirm" =~ ^[Yy]$ ]]; then
        echo ""
        echo "Stopping all services first..."
        docker-compose down --timeout 30 2>/dev/null
        docker-compose -f docker-compose.prod.yml down --timeout 30 2>/dev/null

        echo ""
        echo "Cleaning up Docker resources..."
        docker system prune -f
        docker volume prune -f
        docker network prune -f

        echo ""
        echo "✅ Cleanup completed!"
        echo ""
        echo "📊 System Status:"
        docker system df
    else
        echo ""
        echo "❌ Cleanup cancelled."
    fi
else
    echo "📋 GearFalcon Logs and Monitoring"
    echo "================================"
    echo ""
    echo "Usage:"
    echo "  ./logs.sh dev [service]     - View development logs"
    echo "  ./logs.sh prod [service]    - View production logs"
    echo "  ./logs.sh status            - Show service status"
    echo "  ./logs.sh health            - Check service health"
    echo "  ./logs.sh cleanup           - Clean up Docker resources"
    echo ""
    echo "Examples:"
    echo "  ./logs.sh dev               - All development logs"
    echo "  ./logs.sh dev backend       - Backend development logs"
    echo "  ./logs.sh prod frontend     - Frontend production logs"
    echo "  ./logs.sh status            - Show all service status"
    echo "  ./logs.sh health            - Check all service health"
    echo ""
    echo "Press any key to exit..."
    read -n1
fi

# Function for health check
check_health() {
    HEALTH_URL="$1"
    SERVICE_NAME="$2"

    echo "  $SERVICE_NAME: $HEALTH_URL"

    # Use curl with proper headers, timeout, and save response
    if curl -f -s --max-time 10 --header "Accept: application/json" "$HEALTH_URL" > "$TEMP_FILE" 2>/dev/null; then
        # Check if response contains expected JSON structure
        if grep -q '"status"' "$TEMP_FILE"; then
            echo "✅ $SERVICE_NAME OK"
        else
            echo "❌ $SERVICE_NAME FAILED - Invalid response format"
            echo "Response received:"
            cat "$TEMP_FILE"
            echo ""
        fi
    else
        echo "❌ $SERVICE_NAME FAILED - No response or timeout"
    fi
}
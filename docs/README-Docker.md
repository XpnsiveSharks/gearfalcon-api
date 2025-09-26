# GearFalcon Containerization Guide

This guide explains how to run both the Next.js frontend and PHP backend using Docker containers with enterprise-grade security and management features.

## Project Structure

- **Backend**: PHP 8.3 with security hardening
- **Frontend**: Next.js with App Router
- **Database**: MySQL 8.0 (containerized with environment variables)
- **Scripts**: Organized in `scripts/` folder with OS-specific subfolders (`windows/` and `linux/`)

## Prerequisites

- Docker Desktop (latest version) or Docker Engine on Linux
- Windows 10/11 with WSL2 (recommended) or Linux/Ubuntu

## 🚀 Quick Start

### Using Scripts (Recommended)

#### Windows
```bash
# Start development mode with full validation
.\scripts\windows\start-dev.bat

# Start production mode with security checks
.\scripts\windows\start-prod.bat

# Stop all services with cleanup
.\scripts\windows\stop.bat

# View logs and monitor services
.\scripts\windows\logs.bat
```

#### Linux/Ubuntu
```bash
# Start development mode with full validation
sudo ./scripts/linux/start-dev.sh

# Start production mode with security checks
sudo ./scripts/linux/start-prod.sh

# Stop all services with cleanup
sudo ./scripts/linux/stop.sh

# View logs and monitor services
sudo ./scripts/linux/logs.sh
```

**Note:** Use `sudo` if you encounter Docker permission errors. Alternatively, add your user to the docker group: `sudo usermod -aG docker $USER` (requires logout/login).

### Using Docker Commands (Advanced)

```bash
# Development Mode (with hot reload)
docker-compose up --build

# Production Mode
docker-compose -f docker-compose.prod.yml up --build -d
```

## Access Points

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8080
- **Health Checks**:
  - Frontend Health: http://localhost:3000/api/health
  - Backend Health: http://localhost:8080/health

## 🔒 Security Features

- **Database Isolation**: MySQL is not exposed to host (internal only)
- **Environment Variables**: Secure credential management via docker-compose
- **Resource Limits**: CPU and memory constraints prevent resource exhaustion
- **Health Monitoring**: Automatic service health verification
- **Non-root Containers**: Security hardening with restricted user permissions

## Environment Configuration

### Development Environment

The development environment uses the following configuration:

- **Frontend**: Hot reload enabled, connects to backend on port 8080
- **Backend**: Development mode with source code mounting
- **Database**: MySQL with development data

### Production Environment

For production deployment:

1. Update the `.env.production` file with your production values
2. Update the `docker-compose.prod.yml` file if needed
3. Use the production docker-compose file:

```bash
docker-compose -f docker-compose.prod.yml up --build -d
```

## File Structure

```
gearfalcon-app/
├── docker-compose.yml          # Main docker-compose file
├── docker-compose.override.yml # Development overrides
├── docker-compose.prod.yml     # Production configuration
├── Dockerfile                  # Backend Dockerfile
├── .dockerignore              # Backend ignore file
├── .env.development          # Development environment
├── .env.production           # Production environment
├── scripts/                   # OS-specific scripts
│   ├── windows/               # Windows batch scripts
│   │   ├── generate-secrets.bat
│   │   ├── logs.bat
│   │   ├── start-dev.bat
│   │   ├── start-prod.bat
│   │   └── stop.bat
│   └── linux/                 # Linux shell scripts
│       ├── generate-secrets.sh
│       ├── logs.sh
│       ├── start-dev.sh
│       ├── start-prod.sh
│       └── stop.sh
├── docs/                      # Documentation
│   ├── README-Docker.md       # Complete documentation
│   └── ...
└── README.md                  # Main project README
```

## Development Workflow

### Starting Services

```bash
# Start all services in development mode
docker-compose up --build

# Start in background
docker-compose up --build -d

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f frontend
docker-compose logs -f backend
```

### Making Changes

- **Frontend**: Changes are automatically reflected due to hot reload
- **Backend**: Changes require container restart (or use volume mounting in development)

### Stopping Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: This deletes database data)
docker-compose down -v
```

## Database Management

### Accessing MySQL

```bash
# Connect to MySQL container (development)
docker-compose exec db mysql -u gearfalcon_user -p

# Connect to MySQL container (production)
docker-compose -f docker-compose.prod.yml exec db mysql -u gearfalcon_user -p

# Database credentials are managed through environment variables in docker-compose.yml
# Username: gearfalcon_user
# Password: SF7V8oHYEw1zDgQ9TU2d6OrJnvxXa5qC (defined in docker-compose.yml)
```

### Database Persistence

Database data is persisted in a Docker volume. To reset the database:

```bash
# Stop services
docker-compose down

# Remove database volume
docker volume rm gearfalcon-app_mysql_data

# Start services (database will be recreated)
docker-compose up --build -d
```

## Troubleshooting

### Using Scripts (Recommended)

#### Windows
```bash
# Quick service status and health check
.\scripts\windows\logs.bat status

# View all logs with auto-follow
.\scripts\windows\logs.bat dev

# Check service health
.\scripts\windows\logs.bat health

# Clean up Docker resources
.\scripts\windows\logs.bat cleanup
```

#### Linux/Ubuntu
```bash
# Quick service status and health check
sudo ./scripts/linux/logs.sh status

# View all logs with auto-follow
sudo ./scripts/linux/logs.sh dev

# Check service health
sudo ./scripts/linux/logs.sh health

# Clean up Docker resources
sudo ./scripts/linux/logs.sh cleanup
```

### Common Issues

1. **Port already in use**: Change ports in docker-compose.yml or stop conflicting services
2. **Permission issues**: Make sure Docker has proper permissions (Windows: Docker Desktop, Linux: user in docker group)
3. **Path not found**: Ensure the frontend path in docker-compose.yml is correct
4. **Environment variables**: Ensure docker-compose.yml has correct database credentials
5. **Health check failures**: Services may take time to start - check logs with `.\scripts\windows\logs.bat` (Windows) or `sudo ./scripts/linux/logs.sh` (Linux)

### 🔧 Environment Variables Issues

#### **Issue: Database connection errors**

##### Solution
```bash
# Check that docker-compose.yml has correct credentials
# Database password: SF7V8oHYEw1zDgQ9TU2d6OrJnvxXa5qC
# Root password: FjmsJMI5cU1HpdNROGkBYh8aErWDLZSV

# Test database connection
docker-compose exec db mysql -u gearfalcon_user -pSF7V8oHYEw1zDgQ9TU2d6OrJnvxXa5qC -e "SHOW DATABASES;"
```

#### **Issue: Services won't start**

##### Solution
```bash
# Check service status
docker-compose ps

# View logs for errors
docker-compose logs

# Restart services
docker-compose down && docker-compose up -d
```

### Docker Commands

```bash
# Rebuild specific service
docker-compose build frontend
docker-compose build backend

# Restart specific service
docker-compose restart frontend

# Remove all containers and images
docker-compose down --rmi all

# Clean up unused Docker resources
docker system prune -a

# Check service health manually
curl http://localhost:8080/health
curl http://localhost:3000/api/health
```

## Production Deployment

For production deployment:

1. Update all environment variables in `.env.production`
2. Use the production docker-compose file
3. Use environment variables for sensitive data (recommended)
4. Set up proper logging and monitoring
5. Configure reverse proxy (nginx) if needed

## Team Development Setup

### 🔧 Environment Variables Configuration for Teams

**⚠️ IMPORTANT**: Environment variables in docker-compose.yml contain sensitive information and should **NEVER** be committed to version control with real passwords.

#### **Configuration**

The docker-compose.yml file contains the database credentials as environment variables:

```yaml
db:
  environment:
    MYSQL_ROOT_PASSWORD: FjmsJMI5cU1HpdNROGkBYh8aErWDLZSV
    MYSQL_DATABASE: gearfalcon_db_dev
    MYSQL_USER: gearfalcon_user
    MYSQL_PASSWORD: SF7V8oHYEw1zDgQ9TU2d6OrJnvxXa5qC

backend:
  environment:
    DB_PASSWORD: SF7V8oHYEw1zDgQ9TU2d6OrJnvxXa5qC
```

#### **Team Development**

Each team member uses the same environment variables configuration - no per-user setup required.

### 🔧 Secrets Generation Scripts

#### Windows (generate-secrets.bat)
```batch
@echo off
echo 🔐 Generating Docker Secrets...
echo ================================

REM Check if secrets directory exists
if not exist "secrets" (
    echo 📁 Creating secrets directory...
    mkdir secrets
)

echo 🔑 Generating database password...
powershell -Command "[Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_password.txt -Encoding UTF8"

echo 🔑 Generating root password...
powershell -Command "[Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_root_password.txt -Encoding UTF8"

echo 📝 Setting database name...
echo gearfalcon_db_dev > secrets/db_database.txt

echo 🔒 Setting file permissions...
icacls secrets\* /inheritance:r /grant:r "%username%:F" >nul 2>&1
icacls secrets\* /remove "Users" "Everyone" "Authenticated Users" >nul 2>&1

echo ✅ Secrets generated successfully!
echo 📍 Location: ./secrets/
echo 🔒 Files are secured with owner-only permissions
echo.
echo 🚀 You can now start the development environment:
echo    .\scripts\windows\start-dev.bat
echo.
pause
```

#### Linux/Ubuntu (generate-secrets.sh)
```bash
#!/bin/bash

echo "🔐 Generating Docker Secrets..."
echo "==============================="

# Check if secrets directory exists
if [ ! -d "secrets" ]; then
    echo "📁 Creating secrets directory..."
    mkdir -p secrets
fi

echo "🔑 Generating database password..."
openssl rand -base64 32 > secrets/db_password.txt

echo "🔑 Generating root password..."
openssl rand -base64 32 > secrets/db_root_password.txt

echo "📝 Setting database name..."
echo "gearfalcon_db_dev" > secrets/db_database.txt

echo "🔒 Setting file permissions..."
chmod 600 secrets/*

echo "✅ Secrets generated successfully!"
echo "📍 Location: ./secrets/"
echo "🔒 Files are secured with owner-only permissions"
echo ""
echo "🚀 You can now start the development environment:"
echo "    sudo ./scripts/linux/start-dev.sh"
echo ""
read -p "Press Enter to continue..."
```

### 🔒 Security Best Practices for Teams

#### **✅ DO:**
- ✅ Generate unique secrets for each developer
- ✅ Use cryptographically secure random passwords
- ✅ Set proper file permissions (600/owner-only)
- ✅ Keep secrets out of version control
- ✅ Use different secrets for development vs production

#### **❌ DON'T:**
- ❌ Share secrets between team members
- ❌ Commit secrets to git
- ❌ Use weak or predictable passwords
- ❌ Store secrets in environment variables
- ❌ Use production secrets in development

#### **🔍 Verify Security Setup**

##### Windows
```bash
# Check .gitignore includes secrets
findstr /C:"secrets/" .gitignore

# Verify secrets are not tracked by git
git status --porcelain | findstr secrets

# Check file permissions
dir secrets\ /Q
```

##### Linux/Ubuntu
```bash
# Check .gitignore includes secrets
grep "secrets/" .gitignore

# Verify secrets are not tracked by git
git status --porcelain | grep secrets

# Check file permissions
ls -la secrets/
```

## Security Notes

### 🔒 Production Security Features
- **Database Isolation**: MySQL not exposed to host (internal networking only)
- **Environment Variables**: Secure credential management via docker-compose
- **Non-root Containers**: All services run with restricted user permissions
- **Resource Limits**: CPU and memory constraints prevent resource exhaustion
- **Health Monitoring**: Automatic service health verification

### Security Best Practices
- ✅ **Change all default passwords** before production deployment
- ✅ **Use environment variables** for sensitive configuration (implemented)
- ✅ **Regularly update base images** (PHP 8.3, MySQL 8.0, Node.js latest)
- ✅ **Scan images for vulnerabilities** using Docker security scanning
- ✅ **Monitor resource usage** to detect anomalies
- ✅ **Use health checks** to ensure services are running properly

## Performance Optimization

### ✅ Implemented Optimizations
- **Multi-stage builds**: Reduced image size with production-optimized builds
- **Docker layer caching**: Optimized build process with proper layer ordering
- **Resource limits**: CPU and memory constraints for optimal resource usage
- **Health checks**: Fast service startup validation
- **Volume optimization**: Read-only mounts where appropriate

### Additional Recommendations
- Monitor resource usage with `.\scripts\windows\logs.bat status` (Windows) or `sudo ./scripts/linux/logs.sh status` (Linux)
- Scale resource limits based on actual usage patterns
- Consider using Docker build cache mounts for faster builds
- Use production-optimized base images (already implemented)

## Support

### 🛠️ Getting Help

If you encounter issues:

1. **Quick diagnostics**: Use `.\scripts\windows\logs.bat status` (Windows) or `sudo ./scripts/linux/logs.sh status` (Linux) to check service health
2. **View logs**: Use `.\scripts\windows\logs.bat dev` (Windows) or `sudo ./scripts/linux/logs.sh dev` (Linux) for development logs, or `prod` for production logs
3. **Check health**: Use `.\scripts\windows\logs.bat health` (Windows) or `sudo ./scripts/linux/logs.sh health` (Linux) to verify all services are responding
4. **Clean restart**: Use `.\scripts\windows\stop.bat` then `.\scripts\windows\start-dev.bat` (Windows) or `sudo ./scripts/linux/stop.sh` then `sudo ./scripts/linux/start-dev.sh` (Linux) for a clean restart

### 📋 Advanced Troubleshooting

##### Windows
```bash
# Check Docker system resources
docker system df

# Monitor real-time resource usage
docker stats

# Check for port conflicts
netstat -ano | findstr :3000
netstat -ano | findstr :8080

# Verify Docker Desktop is running
docker version

# Check container networks
docker network ls
```

##### Linux/Ubuntu
```bash
# Check Docker system resources
docker system df

# Monitor real-time resource usage
docker stats

# Check for port conflicts
netstat -tlnp | grep :3000
netstat -tlnp | grep :8080

# Or using ss
ss -tlnp | grep :3000
ss -tlnp | grep :8080

# Verify Docker is running
docker version

# Check container networks
docker network ls
```

### 📞 Support Resources

- **Documentation**: See `DOCKER_README.md` for comprehensive setup guide
- **Architecture**: See `ARCHITECTURE.md` for system design details
- **API Guide**: See `API/README.md` for endpoint documentation
- **Scripts**: Use `scripts/windows/` (Windows) or `scripts/linux/` (Linux) for management scripts

## 🤝 Team Development Workflow

### 🔧 Environment Variables Configuration for Teams

#### **Initial Setup (One-Time)**

##### Windows
```bash
# 1. Clone the repository
git clone <repository-url>
cd gearfalcon-app

# 2. Start development environment (no secrets needed!)
.\scripts\windows\start-dev.bat
```

##### Linux/Ubuntu
```bash
# 1. Clone the repository
git clone <repository-url>
cd gearfalcon-app

# 2. Start development environment (no secrets needed!)
sudo ./scripts/linux/start-dev.sh
```

#### **Daily Development Workflow**

##### Windows
```bash
# Start services
.\scripts\windows\start-dev.bat

# Make your changes
# Frontend: Changes auto-reload at http://localhost:3000
# Backend: Restart container for PHP changes

# Check service health
.\scripts\windows\logs.bat health

# View logs
.\scripts\windows\logs.bat dev

# Stop services
.\scripts\windows\stop.bat
```

##### Linux/Ubuntu
```bash
# Start services
sudo ./scripts/linux/start-dev.sh

# Make your changes
# Frontend: Changes auto-reload at http://localhost:3000
# Backend: Restart container for PHP changes

# Check service health
sudo ./scripts/linux/logs.sh health

# View logs
sudo ./scripts/linux/logs.sh dev

# Stop services
sudo ./scripts/linux/stop.sh
```

#### **Team Communication**
- 📢 **New team members**: Can start immediately with `.\scripts\windows\start-dev.bat` (Windows) or `sudo ./scripts/linux/start-dev.sh` (Linux)
- 📝 **Documentation**: All team members should read this Docker guide
- 🛠️ **Issues**: Use `.\scripts\windows\logs.bat health` (Windows) or `sudo ./scripts/linux/logs.sh health` (Linux) for quick diagnostics

### 🔄 Environment Switching

#### **Development to Production**

##### Windows
```bash
# Stop development
.\scripts\windows\stop.bat

# Update environment variables in docker-compose.prod.yml
# Set production database credentials

# Start production
.\scripts\windows\start-prod.bat
```

##### Linux/Ubuntu
```bash
# Stop development
sudo ./scripts/linux/stop.sh

# Update environment variables in docker-compose.prod.yml
# Set production database credentials

# Start production
sudo ./scripts/linux/start-prod.sh
```

#### **Production to Development**

##### Windows
```bash
# Stop production
.\scripts\windows\stop.bat

# Start development (uses development environment variables)
.\scripts\windows\start-dev.bat
```

##### Linux/Ubuntu
```bash
# Stop production
sudo ./scripts/linux/stop.sh

# Start development (uses development environment variables)
sudo ./scripts/linux/start-dev.sh
```
# Docker Setup Guide

This guide explains how to use the improved Docker Compose configuration with security best practices and comprehensive management scripts.

## 🚀 Quick Start

### Using Scripts (Recommended)

#### Windows
```bash
# Development Environment
.\scripts\windows\start-dev.bat

# Production Environment
.\scripts\windows\start-prod.bat

# Stop all services
.\scripts\windows\stop.bat

# View logs and monitor
.\scripts\windows\logs.bat
```

#### Linux/Ubuntu
```bash
# Development Environment
sudo ./scripts/linux/start-dev.sh

# Production Environment
sudo ./scripts/linux/start-prod.sh

# Stop all services
sudo ./scripts/linux/stop.sh

# View logs and monitor
sudo ./scripts/linux/logs.sh
```

**Note:** Use `sudo` if you encounter Docker permission errors. Alternatively, add your user to the docker group: `sudo usermod -aG docker $USER` (requires logout/login).

### Using Docker Commands (Advanced)
```bash
# Development Environment
docker-compose up -d
docker-compose logs -f
docker-compose down

# Production Environment
docker-compose -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.prod.yml logs -f
docker-compose -f docker-compose.prod.yml down
```

## 🛠️ Management Scripts

The project includes comprehensive scripts for easy Docker management on both Windows and Linux:

### **start-dev** - Development Environment
- ✅ **Progress Tracking**: 5-step startup with validation
- ✅ **Error Handling**: Validates Docker installation and environment
- ✅ **Service Verification**: Confirms services start successfully
- ✅ **User Guidance**: Comprehensive help and monitoring commands
- ✅ **Health Checks**: Verifies service endpoints after startup
- 📁 **Location**: `scripts/windows/start-dev.bat` (Windows) / `scripts/linux/start-dev.sh` (Linux)

### **start-prod** - Production Environment
- ✅ **Security Validation**: Verifies secrets directory and files exist
- ✅ **Environment Checks**: Validates production configuration
- ✅ **Health Verification**: Tests service endpoints after startup
- ✅ **Production Monitoring**: Comprehensive status reporting
- ✅ **Error Prevention**: Prevents startup with missing configuration
- 📁 **Location**: `scripts/windows/start-prod.bat` (Windows) / `scripts/linux/start-prod.sh` (Linux)

### **stop** - Service Shutdown
- ✅ **Graceful Shutdown**: 30-second timeout with proper cleanup
- ✅ **Orphaned Container Cleanup**: Removes leftover containers and networks
- ✅ **System Status**: Shows Docker disk usage and resource information
- ✅ **Maintenance Commands**: Provides cleanup and troubleshooting guidance
- 📁 **Location**: `scripts/windows/stop.bat` (Windows) / `scripts/linux/stop.sh` (Linux)

### **logs** - Monitoring and Logs
- ✅ **Multi-Environment Support**: Separate commands for dev/prod logs
- ✅ **Service-Specific Logs**: Target individual services
- ✅ **Health Monitoring**: Built-in health check functionality
- ✅ **System Cleanup**: Safe Docker resource cleanup utilities
- ✅ **Status Reporting**: Comprehensive service status display
- 📁 **Location**: `scripts/windows/logs.bat` (Windows) / `scripts/linux/logs.sh` (Linux)

##  Security Features

### Database Secrets
The configuration now uses Docker secrets for database credentials:

- **Root Password**: `secrets/db_root_password.txt`
- **User Password**: `secrets/db_password.txt`
- **Database Name**: `secrets/db_database.txt`

### Secret Files Created
- `MySQLR00tP@ssw0rd2024!` (Root password)
- `GearFalc0n_DB_2024!` (User password)
- `gearfalcon_db_prod` (Database name)

**⚠️ IMPORTANT**: Never commit secret files to version control!

## 🐳 Docker Image Improvements

### **Enhanced Dockerfile Features**
- ✅ **PHP 8.3**: Updated from deprecated PHP 8.1 to latest stable
- ✅ **Multi-stage Build**: Optimized image size with production builds
- ✅ **Non-root User**: Security improvement with `gearfalcon` user
- ✅ **Health Checks**: Built-in container health monitoring
- ✅ **Metadata Labels**: Proper container labeling for management
- ✅ **Security Hardening**: Clean package installation and user isolation

### **Service Health Checks**
All services now include comprehensive health checks:

- **Frontend**: Checks `/api/health` endpoint every 30 seconds
- **Backend**: Checks `/health` endpoint every 30 seconds
- **Database**: Uses `mysqladmin ping` every 30 seconds

Services only start when dependencies are healthy, ensuring reliable startup.

## 📊 Resource Management

### Development Limits
- **Frontend**: 0.5 CPU, 512MB RAM
- **Backend**: 1.0 CPU, 1GB RAM
- **Database**: 0.5 CPU, 512MB RAM

### Production Limits
- **Frontend**: 1.0 CPU, 1GB RAM
- **Backend**: 2.0 CPU, 2GB RAM
- **Database**: 1.0 CPU, 1GB RAM

## 📝 Logging

All services use JSON file logging with rotation:
- Max file size: 10MB
- Max files: 3 per service
- Driver: `json-file`

## 🏷️ Service Labels

All services include metadata labels:
- `app=gearfalcon`
- `service=<service-name>`
- `environment=<dev|prod>`

## 🔄 Restart Policies

- **Development**: No restart policy (for debugging)
- **Production**: `unless-stopped` (auto-restart on failure)

## 🌐 Networking

- All services use internal networking
- Database is NOT exposed to host (security improvement)
- Frontend communicates with backend via service name

## 📁 Volume Management

### Development
- Source code mounted read-only for backend
- Node modules and Next.js cache excluded

### Production
- MySQL data persisted in `mysql_data` volume
- Backup volume `mysql_backup` for data protection

## 🚫 Enhanced .dockerignore

The `.dockerignore` file has been significantly improved to exclude unnecessary files from the Docker build context:

### **Excluded File Types**
- ✅ **Version Control**: `.git`, `.gitignore`, `.gitattributes`
- ✅ **Documentation**: `README.md`, `*.md`, `docs/` directory
- ✅ **Environment Files**: `.env*` (but keeps `.env`)
- ✅ **Docker Files**: `Dockerfile*`, `docker-compose*`
- ✅ **Development Files**: `tests/`, `debug/`, `.kilocode/`
- ✅ **IDE Files**: `.vscode/`, `.idea/`, editor swap files
- ✅ **OS Files**: `.DS_Store`, `Thumbs.db`, system files
- ✅ **Temporary Files**: `tmp/`, `temp/`, log files
- ✅ **Secrets**: `secrets/` directory (security)

### **Benefits**
- ⚡ **Faster Builds**: Smaller build context
- 🔒 **Better Security**: Excludes sensitive files
- 🧹 **Cleaner Images**: No development artifacts in production

##  Environment Variables

### Required for Production
```bash
# Set these before running production
export DB_PASSWORD="your-secure-password"
export MYSQL_ROOT_PASSWORD="your-root-password"
export MYSQL_DATABASE="your-database-name"
export MYSQL_USER="your-db-user"
```

## 🚨 Security Improvements

1. **Database Isolation**: MySQL no longer exposed to host
2. **Secret Management**: Credentials stored in Docker secrets
3. **Resource Limits**: Prevents resource exhaustion
4. **Health Monitoring**: Automatic service health verification
5. **Proper Logging**: Centralized, structured logging

## 🛠️ Troubleshooting

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

### Using Docker Commands (Advanced)

#### Check Service Health
```bash
# Development environment
docker-compose ps

# Production environment
docker-compose -f docker-compose.prod.yml ps
```

#### View Resource Usage
```bash
# Monitor resource consumption
docker stats

# Show Docker system usage
docker system df
```

#### Debug Database Connection
```bash
# Access database container (development)
docker-compose exec db mysql -u root -p

# Access database container (production)
docker-compose -f docker-compose.prod.yml exec db mysql -u root -p
```

#### Check Logs
```bash
# Development - All services
docker-compose logs -f

# Development - Specific service
docker-compose logs -f backend

# Development - Follow specific service
docker-compose logs -f frontend

# Production - All services
docker-compose -f docker-compose.prod.yml logs -f

# Production - Specific service
docker-compose -f docker-compose.prod.yml logs -f backend
```

#### Service Health Verification
```bash
# Test backend health endpoint
curl http://localhost:8080/health

# Test frontend health endpoint
curl http://localhost:3000/api/health

# Check all services at once
logs.bat health
```

## 📈 Monitoring

Services include health check endpoints:
- Frontend: `http://localhost:3000/api/health`
- Backend: `http://localhost:8080/health`

## 🔐 Secret Management

To update secrets:

1. Edit the secret files in the `secrets/` directory
2. Restart affected services:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

**Never commit secret files to version control!**

## 🚀 Deployment Checklist

### **Pre-Deployment**
- [ ] Update secret files in `secrets/` directory with production credentials
- [ ] Verify all required secret files exist:
  - [ ] `secrets/db_root_password.txt`
  - [ ] `secrets/db_password.txt`
  - [ ] `secrets/db_database.txt`
- [ ] Test development environment: `.\scripts\windows\start-dev.bat` (Windows) or `sudo ./scripts/linux/start-dev.sh` (Linux)
- [ ] Verify health checks work: `.\scripts\windows\logs.bat health` (Windows) or `sudo ./scripts/linux/logs.sh health` (Linux)
- [ ] Check resource limits are appropriate for your infrastructure

### **Production Deployment**
- [ ] Use production startup script: `.\scripts\windows\start-prod.bat` (Windows) or `sudo ./scripts/linux/start-prod.sh` (Linux)
- [ ] Verify all services start successfully
- [ ] Test health endpoints:
  - [ ] Frontend: `http://localhost:3000/api/health`
  - [ ] Backend: `http://localhost:8080/health`
- [ ] Monitor logs: `.\scripts\windows\logs.bat prod` (Windows) or `sudo ./scripts/linux/logs.sh prod` (Linux)
- [ ] Check service status: `.\scripts\windows\logs.bat status` (Windows) or `sudo ./scripts/linux/logs.sh status` (Linux)

### **Post-Deployment**
- [ ] Verify logging configuration with `.\scripts\windows\logs.bat prod` (Windows) or `sudo ./scripts/linux/logs.sh prod` (Linux)
- [ ] Test backup and recovery procedures
- [ ] Monitor resource usage: `docker stats`
- [ ] Set up log rotation monitoring
- [ ] Document any environment-specific configurations

### **Maintenance**
- [ ] Regular health checks: `.\scripts\windows\logs.bat health` (Windows) or `sudo ./scripts/linux/logs.sh health` (Linux)
- [ ] Monitor resource usage: `.\scripts\windows\logs.bat status` (Windows) or `sudo ./scripts/linux/logs.sh status` (Linux)
- [ ] Clean up resources periodically: `.\scripts\windows\logs.bat cleanup` (Windows) or `sudo ./scripts/linux/logs.sh cleanup` (Linux)
- [ ] Update secrets rotation schedule
- [ ] Review and update resource limits as needed

## 📋 Script Usage Examples

### **Development Workflow**

#### Windows
```bash
# Start development environment
.\scripts\windows\start-dev.bat

# Monitor logs during development
.\scripts\windows\logs.bat dev

# Check service health
.\scripts\windows\logs.bat health

# Stop all services
.\scripts\windows\stop.bat
```

#### Linux/Ubuntu
```bash
# Start development environment
sudo ./scripts/linux/start-dev.sh

# Monitor logs during development
sudo ./scripts/linux/logs.sh dev

# Check service health
sudo ./scripts/linux/logs.sh health

# Stop all services
sudo ./scripts/linux/stop.sh
```

### **Production Workflow**

#### Windows
```bash
# Start production environment
.\scripts\windows\start-prod.bat

# Monitor production logs
.\scripts\windows\logs.bat prod

# Check system status
.\scripts\windows\logs.bat status

# Graceful shutdown
.\scripts\windows\stop.bat
```

#### Linux/Ubuntu
```bash
# Start production environment
sudo ./scripts/linux/start-prod.sh

# Monitor production logs
sudo ./scripts/linux/logs.sh prod

# Check system status
sudo ./scripts/linux/logs.sh status

# Graceful shutdown
sudo ./scripts/linux/stop.sh
```

### **Maintenance Tasks**

#### Windows
```bash
# Clean up Docker resources
.\scripts\windows\logs.bat cleanup

# Check all service health
.\scripts\windows\logs.bat health

# View resource usage
docker stats
```

#### Linux/Ubuntu
```bash
# Clean up Docker resources
sudo ./scripts/linux/logs.sh cleanup

# Check all service health
sudo ./scripts/linux/logs.sh health

# View resource usage
docker stats
```

## 🎉 Summary of Improvements

### **Docker Configuration**
- ✅ **PHP 8.3**: Updated from deprecated PHP 8.1
- ✅ **Multi-stage Build**: Optimized image size
- ✅ **Security Hardening**: Non-root user, clean installation
- ✅ **Health Checks**: Built-in container monitoring
- ✅ **Resource Limits**: CPU and memory constraints
- ✅ **Secret Management**: Secure credential handling

### **Docker Compose**
- ✅ **Database Isolation**: No exposed database ports
- ✅ **Internal Networking**: Secure service communication
- ✅ **Health Dependencies**: Services wait for healthy dependencies
- ✅ **Structured Logging**: JSON logging with rotation
- ✅ **Metadata Labels**: Proper service labeling

### **Management Scripts**
- ✅ **Error Handling**: Comprehensive validation and error reporting
- ✅ **Progress Feedback**: Step-by-step startup process
- ✅ **Security Validation**: Production environment checks
- ✅ **Health Verification**: Automatic service health testing
- ✅ **Cleanup Tools**: Safe resource cleanup utilities

### **Documentation**
- ✅ **Comprehensive Guide**: Complete setup and usage instructions
- ✅ **Troubleshooting**: Detailed problem-solving guide
- ✅ **Best Practices**: Security and performance recommendations
- ✅ **Maintenance**: Regular upkeep and monitoring procedures

**All components now follow enterprise-grade best practices with comprehensive error handling, security validation, and monitoring capabilities.**

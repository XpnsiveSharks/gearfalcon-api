# Docker Setup Guide

This guide explains how to use the improved Docker Compose configuration with security best practices and comprehensive management scripts.

## 🚀 Quick Start

### Using Batch Scripts (Recommended)
```bash
# Development Environment
double-click start-dev.bat

# Production Environment
double-click start-prod.bat

# Stop all services
double-click stop.bat

# View logs and monitor
double-click logs.bat
```

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

The project includes comprehensive batch scripts for easy Docker management:

### **start-dev.bat** - Development Environment
- ✅ **Progress Tracking**: 5-step startup with validation
- ✅ **Error Handling**: Validates Docker installation and environment
- ✅ **Service Verification**: Confirms services start successfully
- ✅ **User Guidance**: Comprehensive help and monitoring commands
- ✅ **Health Checks**: Verifies service endpoints after startup

### **start-prod.bat** - Production Environment
- ✅ **Security Validation**: Verifies secrets directory and files exist
- ✅ **Environment Checks**: Validates production configuration
- ✅ **Health Verification**: Tests service endpoints after startup
- ✅ **Production Monitoring**: Comprehensive status reporting
- ✅ **Error Prevention**: Prevents startup with missing configuration

### **stop.bat** - Service Shutdown
- ✅ **Graceful Shutdown**: 30-second timeout with proper cleanup
- ✅ **Orphaned Container Cleanup**: Removes leftover containers and networks
- ✅ **System Status**: Shows Docker disk usage and resource information
- ✅ **Maintenance Commands**: Provides cleanup and troubleshooting guidance

### **logs.bat** - Monitoring and Logs
- ✅ **Multi-Environment Support**: Separate commands for dev/prod logs
- ✅ **Service-Specific Logs**: Target individual services
- ✅ **Health Monitoring**: Built-in health check functionality
- ✅ **System Cleanup**: Safe Docker resource cleanup utilities
- ✅ **Status Reporting**: Comprehensive service status display

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

### Using Batch Scripts (Recommended)
```bash
# Quick service status and health check
logs.bat status

# View all logs with auto-follow
logs.bat dev

# Check service health
logs.bat health

# Clean up Docker resources
logs.bat cleanup
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
- [ ] Test development environment: `start-dev.bat`
- [ ] Verify health checks work: `logs.bat health`
- [ ] Check resource limits are appropriate for your infrastructure

### **Production Deployment**
- [ ] Use production startup script: `start-prod.bat`
- [ ] Verify all services start successfully
- [ ] Test health endpoints:
  - [ ] Frontend: `http://localhost:3000/api/health`
  - [ ] Backend: `http://localhost:8080/health`
- [ ] Monitor logs: `logs.bat prod`
- [ ] Check service status: `logs.bat status`

### **Post-Deployment**
- [ ] Verify logging configuration with `logs.bat prod`
- [ ] Test backup and recovery procedures
- [ ] Monitor resource usage: `docker stats`
- [ ] Set up log rotation monitoring
- [ ] Document any environment-specific configurations

### **Maintenance**
- [ ] Regular health checks: `logs.bat health`
- [ ] Monitor resource usage: `logs.bat status`
- [ ] Clean up resources periodically: `logs.bat cleanup`
- [ ] Update secrets rotation schedule
- [ ] Review and update resource limits as needed

## 📋 Script Usage Examples

### **Development Workflow**
```bash
# Start development environment
start-dev.bat

# Monitor logs during development
logs.bat dev

# Check service health
logs.bat health

# Stop all services
stop.bat
```

### **Production Workflow**
```bash
# Start production environment
start-prod.bat

# Monitor production logs
logs.bat prod

# Check system status
logs.bat status

# Graceful shutdown
stop.bat
```

### **Maintenance Tasks**
```bash
# Clean up Docker resources
logs.bat cleanup

# Check all service health
logs.bat health

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

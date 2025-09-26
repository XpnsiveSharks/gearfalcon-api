# 📚 GearFalcon Documentation Hub

This is your central navigation point for all GearFalcon project documentation. Each document serves a specific purpose for different audiences and use cases.

## 📖 Documentation Overview

| Document | Purpose | Audience | Key Topics |
|----------|---------|----------|------------|
| **[📋 README.md](../README.md)** | Project overview & quick start | All users | Setup, features, structure |
| **[🐳 Docker Guide](README-Docker.md)** | Containerization & deployment | Developers, DevOps | Docker setup, security, troubleshooting |
| **[🏗️ Architecture](ARCHITECTURE.md)** | System design & patterns | Developers, Architects | Clean architecture, data flow, best practices |
| **[🔌 API Documentation](API/README.md)** | API integration guide | Developers, Integrators | Endpoints, authentication, examples |

## 🚀 Quick Start Guides

### For New Developers
1. **Start here**: Read the main [README.md](../README.md) for project overview
2. **Choose your setup**: Decide between Docker or traditional development
3. **Follow setup guide**: Use either Docker or traditional XAMPP setup
4. **Review architecture**: Understand the system design in [ARCHITECTURE.md](ARCHITECTURE.md)

### For Docker Users
- **[README-Docker.md](README-Docker.md)** - Complete containerization guide
- Includes comprehensive batch scripts: `start-dev.bat`, `start-prod.bat`, `stop.bat`, `logs.bat`
- Enterprise-grade security features with Docker secrets
- Health monitoring, resource management, and automated cleanup
- Production deployment with security validation

### For Traditional Setup Users
- **Main [README.md](../README.md)** - Traditional XAMPP/Apache setup
- Virtual host configuration and local development setup

## 🏗️ Architecture & Design

### [ARCHITECTURE.md](ARCHITECTURE.md)
- **Clean Architecture** implementation details
- **Layer separation** (Domain, Application, Infrastructure, Presentation)
- **Data flow** patterns and best practices
- **Technology stack** and framework choices
- **Business workflows** and entity relationships

**Key Topics:**
- Domain entities (User, Customer, Technician, Quote, Job)
- Repository pattern implementation
- Service layer architecture
- Authentication and authorization flows

## 🔌 API Documentation

### [API/README.md](API/README.md)
- **API endpoint** documentation
- **Authentication** requirements
- **Request/Response** formats
- **Error handling** and status codes
- **Integration examples**

### [API/GearFalcon_API.postman_collection.json](API/GearFalcon_API.postman_collection.json)
- **Postman collection** for API testing
- **Pre-configured requests** for all endpoints
- **Environment variables** setup
- **Example requests** and responses

## 🛠️ Development Workflows

### Docker Development
```bash
# Quick start with validation and health checks
.\start-dev.bat

# Production deployment with security validation
.\start-prod.bat

# Stop services with cleanup
.\stop.bat

# View logs and monitor services
.\logs.bat

# Check service health and status
.\logs.bat health
.\logs.bat status
```

### Traditional Development
```bash
# Install dependencies
composer install

# Setup virtual host
# (see main README.md for detailed instructions)

# Start XAMPP services
# Access via: http://gearfalcon.test
```

## 📁 File Structure

```
docs/
├── README.md                    # This navigation hub
├── README-Docker.md            # Containerization guide
├── ARCHITECTURE.md             # System architecture
├── API/                        # API documentation
│   ├── README.md               # API integration guide
│   └── GearFalcon_API.postman_collection.json
└── DOCKER_README.md            # Comprehensive Docker setup guide

# Project root files
├── start-dev.bat               # Development startup script
├── start-prod.bat              # Production startup script
├── stop.bat                    # Service shutdown script
├── logs.bat                    # Monitoring and logs script
├── secrets/                    # Docker secrets directory
└── DOCKER_README.md            # Complete Docker documentation
```

## 🎯 Key Features Documented

- 🔐 **Authentication System** - JWT tokens, role-based access
- 👥 **Multi-role Support** - Admin, Customer, Technician roles
- 💬 **Quote Management** - Quote lifecycle and job assignment
- 🛒 **Service Catalog** - Dynamic service and pricing management
- 📧 **Email System** - Notifications and verification
- 🏗️ **Clean Architecture** - Maintainable and scalable design

## 📞 Getting Help

If you encounter issues:

1. **Quick diagnostics**: Use `logs.bat status` to check service health
2. **View logs**: Use `logs.bat dev` or `logs.bat prod` for comprehensive logging
3. **Check health**: Use `logs.bat health` to verify all services are responding
4. **Clean restart**: Use `stop.bat` then `start-dev.bat` for a clean restart
5. **Review troubleshooting**: See [README-Docker.md](README-Docker.md) troubleshooting section
6. **API issues**: Check [API/README.md](API/README.md) for endpoint documentation
7. **Architecture questions**: Review [ARCHITECTURE.md](ARCHITECTURE.md) for design patterns
8. **Docker setup**: See [DOCKER_README.md](../DOCKER_README.md) for comprehensive Docker guide

## 🤝 Contributing

Before contributing, please read:
- [ARCHITECTURE.md](ARCHITECTURE.md) - Understanding the system design
- [README-Docker.md](README-Docker.md) - Development environment setup
- [API/README.md](API/README.md) - API integration guidelines

---

**Need help?** Start with the main [project README](../README.md) for a complete overview, then dive into the specific documentation you need!
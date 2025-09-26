# GearFalcon - Service Management System

A full-stack service management application with Next.js frontend and PHP backend, featuring role-based access for customers, technicians, and administrators.

## 🚀 Quick Start

### Option 1: Docker Containerization (Recommended)

#### Windows
```bash
# 1. Generate your own Docker secrets (one-time setup)
.\scripts\windows\generate-secrets.bat

# 2. Start all services with hot reload
.\scripts\windows\start-dev.bat
```

#### Linux/Ubuntu
```bash
# 1. Generate your own Docker secrets (one-time setup)
./scripts/linux/generate-secrets.sh

# 2. Start all services with hot reload
./scripts/linux/start-dev.sh
```

**Access Points:**
- **Frontend**: http://localhost:3000 (Next.js with hot reload)
- **Backend API**: http://localhost:8080 (PHP/Apache)
- **Database**: localhost:3306 (MySQL)

### 🔐 Team Setup
Each team member must generate their own Docker secrets for local development:
1. **Windows**: Run `.\scripts\windows\generate-secrets.bat` to create secure credentials
2. **Linux**: Run `./scripts/linux/generate-secrets.sh` to create secure credentials
3. Secrets are automatically configured with proper security permissions
4. **Never commit secrets to git** - they stay local to your machine

### Option 2: Traditional Setup
See detailed setup instructions in `docs/README.md`

## 📚 Documentation

All project documentation has been organized in the `docs/` folder:

- **[📖 Main Documentation](docs/README.md)** - Complete setup and usage guide
- **[🐳 Docker Guide](docs/README-Docker.md)** - Containerization and deployment
- **[🏗️ Architecture](docs/ARCHITECTURE.md)** - System architecture and design patterns
- **[🔌 API Documentation](docs/API/README.md)** - API endpoints and integration guide

## 🛠️ Development

### Prerequisites
- Docker Desktop (for containerized development)
- PHP 8.1+ and Composer (for traditional development)
- Node.js 18+ (for frontend development)

### Project Structure
```
gearfalcon-app/
├── docs/                    # 📚 All documentation
├── src/                     # 🔧 Backend source code
├── public/                  # 🌐 Public web assets
├── docker-compose.yml       # 🐳 Docker configuration
├── Dockerfile              # 📦 Backend container
├── composer.json           # 📦 PHP dependencies
└── start-dev.bat           # 🚀 Quick start script

gearfalcon-frontend/         # ⚛️ Next.js frontend
├── src/app/                # 📱 App router pages
├── Dockerfile              # 📦 Frontend container
└── package.json            # 📦 Node.js dependencies
```

## 🎯 Key Features

- 🔐 **JWT Authentication** with role-based access control
- 👥 **Multi-role System** (Admin, Customer, Technician)
- 💬 **Quote Management** with job assignment workflow
- 🛒 **Service Catalog** with dynamic pricing
- 📧 **Email Notifications** and verification system
- 📱 **Responsive Design** with modern UI/UX

## 🤝 Contributing

Please read our [Contributing Guide](docs/CONTRIBUTING.md) for development guidelines and best practices.

## 📄 License

This project is proprietary software. All rights reserved.

---

**For detailed documentation, see:** `docs/README.md`

**Quick access to docs:**
- **[📖 Main Guide](docs/README.md)** - Complete setup and usage
- **[🐳 Docker Guide](docs/README-Docker.md)** - Containerization
- **[🏗️ Architecture](docs/ARCHITECTURE.md)** - System design
- **[🔌 API Docs](docs/API/README.md)** - API integration
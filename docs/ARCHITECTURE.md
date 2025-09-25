## The purpose of each layer

### Domain Layer (Infrastructure/Models/)
- The core of the business logic for GearFalcon.
- Contains entities, value objects, and domain events.
- Defines what the system is, independent of technical details.
- **Examples**:
  - `User.php` - Core user entity with roles, status, and business rules
  - `Customer.php` - Customer profile and contact information
  - `Technician.php` - Technician skills, availability, and assignments
  - `Job.php` - Service job lifecycle and status management
  - `Quote.php` - Quote generation and pricing logic
  - `Service.php`, `ServiceCategory.php` - Service catalog and categorization
- No external dependencies (e.g., no database code here).

### Application Layer (Application/)
- The orchestrator of use cases for GearFalcon's business workflows.
- Coordinates between domain objects and infrastructure.
- Defines what the system does (use cases like Register Customer, Create Quote, Assign Job, etc.).
- **Examples**:
  - `AuthService.php` - Handles authentication and JWT token management
  - `CustomerRegistrationService.php` - Manages customer onboarding workflow
  - `QuoteService.php` - Orchestrates quote creation and job assignment
  - `JobAssignmentService.php` - Handles technician job assignments
  - `EmailVerificationService.php` - Manages email verification process
- Does not contain business rules itself, just application workflows.

### Infrastructure Layer (Infrastructure/)
- The technical implementation details for GearFalcon.
- Provides actual implementations of abstractions (e.g., repositories, database, container).
- **Examples**:
  - `Database/Database.php` - Eloquent ORM integration with MySQL
  - `Container.php` - Dependency injection container setup
  - `Repositories/` - All repository implementations (UserRepository, CustomerRepository, JobRepository, etc.)
  - Maps domain entities to database operations
- Knows about external concerns like persistence, networking, or frameworks.
- Supports Domain and Application layers but doesn't contain business rules.

### Presentation Layer (Presentation/)
- The entry point for users or external systems in GearFalcon.
- Handles incoming HTTP requests (controllers, middleware, routes).
- Translates user input → Application commands, and Application results → HTTP responses.
- **Examples**:
  - `AuthController.php` - Handles login, registration, token refresh
  - `Customer/QuoteController.php` - Manages quote requests and responses
  - `Admin/UserController.php` - Admin user management endpoints
  - `Technician/TechnicianController.php` - Technician dashboard and assignments
  - `Middleware/AuthMiddleware.php` - JWT authentication middleware
  - `Routes/routes.php` - API route definitions
- Concerned with how the system is exposed, not the business rules.
## 👉 In short
- **Domain** → Business logic (User, Customer, Technician, Quote, Job entities).
- **Application** → Coordinates use cases (Auth, Registration, Quote creation, Job assignment).
- **Infrastructure** → Technical details (MySQL, Eloquent ORM, repositories).
- **Presentation** → Entry point (REST API controllers, JWT middleware).

---

## 🛠️ Technology Stack

### Backend Framework
- **PHP 8.3** - Core programming language (updated from 8.1)
- **Apache 2.4** - Web server for production
- **Composer** - PHP dependency management

### Database & ORM
- **MySQL 8.0** - Primary database
- **Illuminate/Database** - Eloquent ORM (Laravel's database component)
- **Migration Support** - Database schema management

### Authentication & Security
- **Firebase JWT** - JSON Web Token implementation
- **Password Hashing** - Secure password storage
- **CORS Support** - Cross-origin resource sharing

### External Services
- **PHPMailer** - Email sending capabilities
- **DotEnv** - Environment configuration management

### Development Tools
- **PSR-4 Autoloading** - Standard PHP autoloading
- **Clean Architecture** - Separation of concerns
- **Dependency Injection** - Service container pattern

### Containerization & Deployment
- **Docker** - Containerized deployment with security hardening
- **Docker Compose** - Multi-service orchestration
- **Docker Secrets** - Secure credential management
- **Health Checks** - Service monitoring and validation
- **Resource Limits** - CPU and memory constraints
- **Batch Scripts** - Windows-optimized management tools

---

## 🔄 Key Business Workflows

### 1. Customer Registration & Authentication
```
Customer Request → AuthController → AuthService → UserRepository → Database
     ↓
Email Verification → EmailVerificationService → PHPMailer → Email Sent
```

### 2. Quote Creation & Job Assignment
```
Customer Quote Request → QuoteController → QuoteService → QuoteRepository → Database
     ↓
Job Creation → JobService → JobRepository → Database
     ↓
Technician Assignment → JobAssignmentService → TechnicianRepository → Database
```

### 3. Service Management
```
Admin Service Creation → AdminController → ServiceCatalogService → ServiceRepository → Database
     ↓
Customer Service Selection → Quote Creation → Service Integration
```

### 4. User Role Management
```
Multi-Role System (Admin/Customer/Technician)
     ↓
Role-Based Access Control → Middleware → Controller Authorization
     ↓
Feature-Specific Services → Role-Appropriate Business Logic
```

---

## 🎯 Architecture Benefits for GearFalcon

### ✅ Testability
- **Isolated Testing**: Each layer can be unit tested independently
- **Mock External Dependencies**: Repository interfaces allow easy mocking
- **Domain Logic Testing**: Pure business logic without infrastructure concerns

### ✅ Maintainability
- **Single Responsibility**: Each class has one clear purpose
- **Easy Refactoring**: Changes in one layer don't affect others
- **Clear Dependencies**: Explicit interface contracts

### ✅ Scalability
- **Horizontal Scaling**: Infrastructure layer can be easily replaced
- **Feature Addition**: New features follow established patterns
- **Team Development**: Multiple developers can work on different layers

### ✅ Flexibility
- **Technology Changes**: Database or framework can be swapped
- **Multi-Interface Support**: Same domain logic, different presentation layers
- **Microservices Ready**: Can be split into separate services if needed

---

## 📋 Best Practices Implemented

### ✅ Dependency Injection
- Services injected through constructor parameters
- Interface-based dependencies, not concrete classes
- Easy to test and maintain

### ✅ Repository Pattern
- Clean separation between domain and data access
- Consistent data access interface across all entities
- Easy to switch between different data sources

### ✅ Service Layer Pattern
- Business workflows separated from controllers
- Reusable business logic across different endpoints
- Clear transaction boundaries

### ✅ Middleware Pattern
- Cross-cutting concerns handled centrally
- Consistent request/response processing
- Easy to add new middleware for new requirements

### ✅ Error Handling
- Centralized error handling through middleware
- Consistent error response format
- Proper HTTP status codes

---

## 🔍 Code Organization

```
src/
├── Application/           # Use cases and business workflows
│   ├── Services/         # Core application services
│   ├── Customer/Services/# Customer-specific services
│   ├── Admin/Services/   # Admin-specific services
│   ├── Technician/Services/# Technician-specific services
│   └── Exceptions/       # Application-specific exceptions
├── Infrastructure/       # Technical implementations
│   ├── Models/          # Domain entities (Eloquent models)
│   ├── Repositories/    # Data access implementations
│   ├── Database/        # Database connection and setup
│   └── Container.php    # Dependency injection container
└── Presentation/        # HTTP interface layer
    ├── Controllers/     # HTTP request handlers
    ├── Middleware/      # Request/response processing
    ├── Http/           # HTTP kernel and routing
    └── Routes/         # Route definitions
```
## How data flows in GearFalcon

### Step 1: Controller (Presentation Layer)
- User sends HTTP request (e.g., POST /api/auth/login or POST /api/customer/quotes).
- Controller receives request (e.g., `AuthController.php` or `Customer/QuoteController.php`).
- Validates input using middleware and request parsing.
- Calls appropriate Application Service.

### Step 2: Application Service (Application Layer)
- **Example**: `AuthService.php` for login flow:
  - Validates credentials using domain entities
  - Creates JWT tokens using Firebase\JWT
  - Calls UserRepositoryInterface to verify user status
- **Example**: `QuoteService.php` for quote creation:
  - Orchestrates quote creation workflow
  - Validates customer and service information
  - Calls multiple repositories (QuoteRepository, JobRepository)

### Step 3: Domain Layer
- Domain entities enforce business rules:
  - `User.php` validates authentication state and role permissions
  - `Customer.php` manages customer profile and contact validation
  - `Quote.php` handles quote lifecycle and pricing rules
  - `Job.php` manages job status transitions and assignments
- Domain events fired (e.g., QuoteCreated, UserLoggedIn).
- No direct database logic - pure business logic.

### Step 4: Repository (Infrastructure Layer)
- Application services use repository interfaces:
  - `UserRepositoryInterface` → `UserRepository.php`
  - `QuoteRepositoryInterface` → `QuoteRepository.php`
  - `CustomerRepositoryInterface` → `CustomerRepository.php`
- Repository implementations map domain entities to database operations.
- Uses Eloquent ORM for database interactions.

### Step 5: Database (Infrastructure Layer)
- `Database/Database.php` provides Eloquent Capsule setup.
- MySQL database stores all application data.
- Handles transactions, migrations, and data persistence.
- Returns results back through repository layer.

### Step 6: Response Formation
- Application service returns domain objects or DTOs.
- Controller formats response (JSON API responses).
- Middleware handles CORS, authentication, error responses.
- Final HTTP response sent to client.

## 👉 In short
```bash
HTTP Request → Controller → Application Service → Domain Entities → Repository → Database → Repository → Application Service → Controller → JSON Response
```
---
## Relationships in GearFalcon

### Core Domain Entities (Infrastructure/Models/)
- **User Aggregate** (`User.php`)
    - Core business concept representing system users with roles (Admin, Customer, Technician).
    - Enforces authentication rules, status management, and role-based permissions.
    - Contains relationships to Customer and Technician entities.

- **Customer Entity** (`Customer.php`)
    - Manages customer profiles, contact information, and service history.
    - Handles customer-specific business rules and validations.
    - Links to quotes, jobs, and address information.

- **Technician Entity** (`Technician.php`)
    - Manages technician profiles, skills, availability, and assignments.
    - Handles skill certifications and scheduling constraints.
    - Links to job assignments and service capabilities.

- **Quote Entity** (`Quote.php`)
    - Manages quote lifecycle from creation to acceptance.
    - Handles pricing calculations and service selections.
    - Converts to jobs upon customer approval.

- **Job Entity** (`Job.php`)
    - Manages service job lifecycle and status transitions.
    - Handles job assignments, scheduling, and completion.
    - Tracks job progress and technician assignments.

### Repository Interfaces & Implementations
- **UserRepositoryInterface** → `UserRepository.php`
    - Defines user persistence and retrieval operations.
    - Handles authentication, role management, and user queries.

- **CustomerRepositoryInterface** → `CustomerRepository.php`
    - Manages customer data persistence and profile operations.
    - Handles customer registration and profile updates.

- **QuoteRepositoryInterface** → `QuoteRepository.php`
    - Manages quote creation, updates, and retrieval.
    - Handles quote-to-job conversion workflows.

- **JobRepositoryInterface** → `JobRepository.php`
    - Manages job lifecycle, assignments, and status tracking.
    - Handles job scheduling and technician assignments.

### Application Services (Application/Services/ & Application/*/Services/)
- **AuthService.php** - Coordinates authentication workflows and JWT management.
- **CustomerRegistrationService.php** - Handles customer onboarding and profile creation.
- **QuoteService.php** - Orchestrates quote creation and job assignment workflows.
- **EmailVerificationService.php** - Manages email verification processes.
- **JobAssignmentService.php** - Handles technician job assignments and scheduling.

### Controllers (Presentation/Controllers/)
- **AuthController.php** - Handles login, registration, token refresh, logout.
- **Customer/QuoteController.php** - Manages customer quote requests and responses.
- **Customer/CustomerController.php** - Handles customer profile management.
- **Admin/UserController.php** - Manages admin user operations.
- **Technician/TechnicianController.php** - Handles technician dashboard and assignments.

### Middleware (Presentation/Middleware/)
- **AuthMiddleware.php** - JWT authentication and authorization.
- **CorsMiddleware.php** - Cross-origin resource sharing handling.
- **ErrorHandlerMiddleware.php** - Global error handling and formatting.

## 👉 In one sentence:
- 👉 Domain entities (User, Customer, Technician, Quote, Job) define business rules and are persisted through repository interfaces,
- 👉 implemented by concrete repositories (Infrastructure). Application services orchestrate these interactions,
- 👉 while controllers expose them over HTTP APIs with proper middleware protection.
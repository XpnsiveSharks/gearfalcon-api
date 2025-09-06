## The purpose of each layer
### Domain Layer
- The core of the business logic.
- Contains entities, value objects, domain services, and domain events.
- Defines what the system is, independent of technical details.
- Example: User, Booking, ServiceCategory define business rules like user activation, booking status updates, etc.
- No external dependencies (e.g., no database code here).
### Application Layer
- The orchestrator of use cases.
- Coordinates between domain objects and infrastructure.
- Defines what the system does (use cases like Register User, Update Profile, etc.).
- Example: RegisterUserHandler.php calls domain services/entities and tells infrastructure to persist changes.
- Does not contain business rules itself, just application workflows.
### Infrastructure Layer
- The technical implementation details.
- Provides actual implementations of abstractions (e.g., repositories, database, container).
- Example: UsersRepository.php implements UserRepositoryInterface.
- Knows about external concerns like persistence, networking, or frameworks.
- Supports Domain and Application layers but doesn’t contain business rules.
### Presentation Layer
- The entry point for users or external systems.
- Handles incoming requests (HTTP controllers, middleware, routes).
- Translates user input → Application commands, and Application results → HTTP responses.
- Example: UserController.php takes request data, calls RegisterUserHandler, and returns a response.
- Concerned with how the system is exposed, not the business rules.
## 👉 In short
- Domain → Business logic (the "heart").
- Application → Coordinates use cases.
- Infrastructure → Technical details (DB, persistence, frameworks).
- Presentation → Entry point (controllers, APIs).
## How data flows
### Step 1: Controller (Presentation Layer)
- The user sends an HTTP request (e.g., POST /users/register).
- UserController receives the request.
- It validates/parses input and creates a command or DTO.
- Passes the request to the Application Handler.
### Step 2: Application Handler (Application Layer)
- Example: RegisterUserHandler.
- Orchestrates the use case:
- Creates domain objects (User, Profile, Credentials, etc.).
- Uses domain services if needed (CreateUserService).
- Calls the Repository Interface (UserRepositoryInterface).
### Step 3: Domain Layer
- The domain ensures all business rules are respected.
- Example: User entity validates role, enforces active state, fires domain events (UserRegistered).
- No direct database logic.
- Returns entities/value objects back to the handler.
### Step 4: Repository (Infrastructure Layer)
- The application handler only sees the interface (UserRepositoryInterface).
- The actual implementation (UsersRepository) lives in Infrastructure.
- It maps domain entities to persistence models (via toArray() / hydration).
- Calls the Database service.
### Step 5: Database (Infrastructure Layer)
- Repository uses Database.php or UnitOfWork.php to execute SQL.
- Data is inserted/updated in the database.
- Returns the result (e.g., user ID).
### Step 6: Back to Controller
- The handler returns a result (e.g., the new User object).
- The controller formats the response (JSON, HTML, etc.) and sends it back to the client.
## 👉 In short
```bash
Controller → Application Handler → Domain → Repository → Database → Repository → Application Handler → Controller → Response
```
---
## Relationships
- User Aggregate (Domain/User/User.php)
    - Represents the core business concept of a system user.
    - Holds value objects (Profile, ContactInfo, Credentials, Address) and enforces business rules.
- UserRepositoryInterface (Domain/User/Repositories/UserRepositoryInterface.php)
    - Defines how the application can persist or retrieve User aggregates.
    - The domain depends only on this interface, not on the actual database.
- UsersRepository (Infrastructure/Repositories/UsersRepository.php)
    - Implements UserRepositoryInterface.
    - Knows how to map User aggregates to database rows (toArray()) and back (fromArray()).
- Application Handlers / Services (Application/User/… and Domain/User/Services/…)
    - Coordinate use cases.
    - Example: RegisterUserHandler calls CreateUserService, which uses UserRepositoryInterface to persist the new User.
- Controllers (Presentation/Http/Controllers/UserController.php)
    - Entry point for HTTP requests.
    - Translate input → calls handler → gets a User → outputs JSON/HTTP response.
- Events (Domain/User/Events/…)
    - Fired by the User aggregate (UserRegistered, UserUpdated, etc.).
    - Can be consumed by other services, notifications, or workflows.
## 👉 In one sentence:
- 👉 The User aggregate (Domain) is persisted through the UserRepositoryInterface (Domain) which is implemented by UsersRepository 
- 👉 (Infrastructure). Application handlers orchestrate these interactions, while controllers expose them over HTTP.
# Architecture Documentation

## System Overview

The PQueue system follows a layered architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────┐
│                    Application Layer                     │
│  (Worker.php, Enqueue.php - Entry Points)              │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│                  Application Facade                      │
│  (Bootstrap, Dependency Injection, Service Resolution)   │
└────────────────────┬────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
┌───────▼────────┐      ┌─────────▼──────────┐
│  Service Layer │      │   Queue Layer      │
│  - JobProcessor│      │  - DatabaseQueue   │
│  - WorkerService│     │  - QueueInterface  │
└───────┬────────┘      └─────────┬──────────┘
        │                         │
        └────────────┬────────────┘
                     │
        ┌────────────▼────────────┐
        │   Repository Layer       │
        │  - JobRepository         │
        │  - RepositoryInterface   │
        └────────────┬─────────────┘
                     │
        ┌────────────▼────────────┐
        │   Data Layer            │
        │  - PDO/MySQL            │
        └─────────────────────────┘
```

## Component Relationships

### 1. Application (Facade Pattern)
- **Purpose**: Provides a simplified interface to the complex subsystem
- **Location**: `src/Application.php`
- **Responsibilities**:
  - Bootstrap dependencies
  - Manage Dependency Injection Container
  - Provide convenient access methods

### 2. Dependency Injection Container
- **Purpose**: Manages object creation and dependencies
- **Location**: `src/Support/Container.php`
- **Pattern**: Service Locator
- **Features**:
  - Automatic dependency resolution
  - Singleton support
  - Closure-based factories

### 3. Queue System (Strategy Pattern)
- **Interface**: `src/Contracts/QueueInterface.php`
- **Implementation**: `src/Queue/DatabaseQueue.php`
- **Purpose**: Allows different queue backends
- **Future**: Can add RedisQueue, SqsQueue, etc.

### 4. Repository Pattern
- **Interface**: `src/Contracts/RepositoryInterface.php`
- **Implementation**: `src/Repository/JobRepository.php`
- **Purpose**: Abstracts database operations
- **Benefits**: Easy to test, swap implementations

### 5. Job System

#### Job Interface (Command Pattern)
- **Location**: `src/Contracts/JobInterface.php`
- **Purpose**: Defines contract for all jobs

#### BaseJob (Template Method Pattern)
- **Location**: `src/Jobs/BaseJob.php`
- **Purpose**: Provides job structure
- **Template Methods**:
  - `handle()` - Public interface (template)
  - `before()` - Hook before execution
  - `execute()` - Abstract, must implement
  - `after()` - Hook after execution

#### JobFactory (Factory Pattern)
- **Location**: `src/Jobs/JobFactory.php`
- **Purpose**: Creates job instances
- **Methods**:
  - `create()` - From class name and args
  - `fromPayload()` - From serialized data

### 6. Event System (Observer Pattern)
- **Interface**: `src/Contracts/EventDispatcherInterface.php`
- **Implementation**: `src/Events/EventDispatcher.php`
- **Purpose**: Decoupled event handling
- **Events**:
  - `job.processing`
  - `job.processed`
  - `job.failed`
  - `job.failed_permanently`

### 7. Service Layer
- **JobProcessor**: Handles job execution and retry logic
- **WorkerService**: Manages worker loop and job processing flow

## Data Flow

### Enqueue Flow
```
Enqueue.php
    ↓
Application::queue()
    ↓
DatabaseQueue::push()
    ↓
JobRepository::create()
    ↓
MySQL Database
```

### Worker Flow
```
Worker.php
    ↓
Application::worker()
    ↓
WorkerService::start()
    ↓
DatabaseQueue::pop()
    ↓
JobProcessor::process()
    ↓
JobFactory::fromPayload()
    ↓
Job::handle()
    ↓
EventDispatcher::dispatch()
```

## Design Pattern Usage Summary

| Pattern | Purpose | Location |
|---------|---------|----------|
| **Facade** | Simplified interface | `Application.php` |
| **Service Locator** | Dependency management | `Container.php` |
| **Strategy** | Queue implementations | `QueueInterface.php` |
| **Repository** | Data access abstraction | `RepositoryInterface.php` |
| **Factory** | Job creation | `JobFactory.php` |
| **Template Method** | Job structure | `BaseJob.php` |
| **Observer** | Event handling | `EventDispatcher.php` |
| **Command** | Job interface | `JobInterface.php` |
| **Singleton** | Shared services | `Logger.php`, `Config.php` |

## SOLID Principles

### Single Responsibility Principle (SRP)
- Each class has one reason to change
- Example: `JobProcessor` only handles job execution
- Example: `JobRepository` only handles data access

### Open/Closed Principle (OCP)
- Open for extension, closed for modification
- Example: Add new queue implementations without modifying existing code
- Example: Extend `BaseJob` without modifying it

### Liskov Substitution Principle (LSP)
- Subtypes must be substitutable for their base types
- Example: Any `QueueInterface` implementation can be used
- Example: Any `JobInterface` implementation can be processed

### Interface Segregation Principle (ISP)
- Clients shouldn't depend on interfaces they don't use
- Example: Separate interfaces for different concerns
- Example: `RepositoryInterface` only has data access methods

### Dependency Inversion Principle (DIP)
- Depend on abstractions, not concretions
- Example: Services depend on `QueueInterface`, not `DatabaseQueue`
- Example: Services depend on `RepositoryInterface`, not `JobRepository`

## Testing Strategy

With this architecture, testing is easier:

1. **Mock Dependencies**: Use interfaces for easy mocking
2. **Test Services**: Test business logic in isolation
3. **Test Repositories**: Test data access separately
4. **Integration Tests**: Test full flow with test database

## Extension Points

### Adding a New Queue Backend
1. Implement `QueueInterface`
2. Register in `Application::bootstrap()`
3. Done!

### Adding a New Job Type
1. Extend `BaseJob` or implement `JobInterface`
2. Use `JobFactory` to create instances
3. Push to queue

### Adding Event Listeners
1. Get `EventDispatcher` from Application
2. Call `listen()` with event name and callback
3. Events automatically fire during job processing

### Adding Custom Services
1. Create service class
2. Register in `Application::bootstrap()`
3. Access via `$app->make(YourService::class)`

## Performance Considerations

1. **Connection Pooling**: PDO connection reused via singleton
2. **Lazy Loading**: Services created only when needed
3. **Event System**: Lightweight, no external dependencies
4. **Repository Pattern**: Can cache queries if needed

## Security Considerations

1. **SQL Injection**: Prevented by PDO prepared statements
2. **Job Serialization**: Jobs serialized securely
3. **Input Validation**: Type hints enforce type safety
4. **Error Handling**: Exceptions don't expose sensitive data


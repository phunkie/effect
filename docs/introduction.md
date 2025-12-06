# Introduction to Phunkie Effect

## What is Phunkie Effect?

Phunkie Effect is a functional effects library for PHP that brings the power of purely functional programming to side effect management. Inspired by Scala's cats-effect and built on top of the Phunkie functional programming library, it provides a robust framework for writing predictable, composable, and testable code.

At its core, Phunkie Effect introduces the `IO` monad - a data structure that represents a computation that may perform side effects. Unlike traditional imperative code where side effects happen immediately, `IO` allows you to describe what should happen without actually executing it. This separation of description from execution is the key to writing purely functional code in PHP.

```php
use Phunkie\Effect\IO;

// This doesn't execute anything - it just describes what should happen
$program = IO::of(fn() => file_get_contents('config.json'))
    ->map(fn($json) => json_decode($json, true))
    ->flatMap(fn($config) => IO::of(fn() => connectToDatabase($config['db'])));

// The program only runs when you explicitly call unsafeRun()
$result = $program->unsafeRun();
```

## Philosophy and Design Goals

### Referential Transparency

Phunkie Effect is built on the principle of referential transparency - the idea that you should be able to replace an expression with its value without changing the program's behavior. This makes code easier to reason about, test, and refactor.

```php
// Without IO - side effects happen immediately
$data = file_get_contents('data.txt'); // Executes now!
$result = processData($data);

// With IO - side effects are described, not executed
$program = IO::of(fn() => file_get_contents('data.txt'))
    ->map(fn($data) => processData($data));
// Nothing has executed yet - we can compose, test, and reason about it
```

### Composability

Effects should compose just like pure functions. Phunkie Effect provides a rich set of combinators that allow you to build complex programs from simple building blocks.

```php
$readConfig = IO::of(fn() => file_get_contents('config.json'));
$parseJson = fn($json) => IO::of(fn() => json_decode($json, true));
$validateConfig = fn($config) => IO::of(fn() => validate($config));

// Compose them together
$program = $readConfig
    ->flatMap($parseJson)
    ->flatMap($validateConfig);
```

### Resource Safety

Managing resources (files, database connections, network sockets) is error-prone. Phunkie Effect provides automatic resource management through brackets and the Resource type, ensuring cleanup happens even when errors occur.

```php
use Phunkie\Effect\Resource;

$program = Resource::make(
    IO::of(fn() => fopen('file.txt', 'r')),
    fn($handle) => IO::of(fn() => fclose($handle))
)->use(fn($handle) => 
    IO::of(fn() => fread($handle, 1024))
);
```

### Testability

By separating effect description from execution, your code becomes trivially testable. You can test the logic without performing actual side effects.

```php
// Your business logic returns IO values
function processOrder(Order $order): IO {
    return saveToDatabase($order)
        ->flatMap(fn() => sendConfirmationEmail($order->email))
        ->flatMap(fn() => updateInventory($order->items));
}

// In tests, you can inspect the IO structure without executing it
$io = processOrder($testOrder);
// Assert on the structure, or provide test implementations
```

### Concurrency and Parallelism

Phunkie Effect provides safe, composable concurrency primitives that make it easy to write concurrent programs without the typical pitfalls of shared mutable state.

```php
use Phunkie\Effect\IO;

// Run multiple effects in parallel
$results = IO::parSequence([
    fetchUserData($userId),
    fetchOrderHistory($userId),
    fetchPreferences($userId)
]);
```

## When to Use Phunkie Effect

### Perfect For:

**Complex Applications with Many Side Effects**
- Web applications with database access, external APIs, file I/O
- CLI tools that interact with the filesystem and network
- Background workers and job processors

**Applications Requiring High Reliability**
- Financial systems where correctness is critical
- Healthcare applications with strict error handling requirements
- Any system where bugs have serious consequences

**Concurrent and Parallel Processing**
- Data processing pipelines
- Microservices that need to coordinate multiple operations
- Applications that benefit from parallel execution

**Testable Code**
- When you need comprehensive test coverage
- When you want to test business logic without hitting real databases/APIs
- When you need to verify error handling paths

### Consider Alternatives When:

**Simple Scripts**
- One-off scripts with minimal side effects
- Quick prototypes where correctness isn't critical

**Performance-Critical Hot Paths**
- While Phunkie Effect is reasonably performant, the abstraction overhead may not be suitable for extremely performance-sensitive code
- Consider using it for the application structure and dropping down to imperative code for hot paths

**Team Unfamiliar with Functional Programming**
- There's a learning curve to functional programming concepts
- Ensure your team is willing to invest in learning these patterns

## Getting Started

Ready to dive in? Check out the [Getting Started](getting-started.md) guide to install Phunkie Effect and write your first effectful program.

## Learn More

- [IO App and Console](io-app.md) - Build command-line applications
- [Combinators](combinators.md) - Core operations for working with IO
- [Concurrency](concurrency.md) - Parallel and concurrent execution
- [Resources](resources.md) - Safe resource management

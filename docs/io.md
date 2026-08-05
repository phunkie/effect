# IO - The Effect Monad

The `IO` monad is the core abstraction in Phunkie Effect for managing side effects in a purely functional way. It represents a computation that performs side effects, but delays their execution until explicitly run.

## Table of Contents

- [Core Concepts](#core-concepts)
- [Creating IO](#creating-io)
- [Transforming IO](#transforming-io)
- [Combining IO](#combining-io)
- [Async Execution](#async-execution)
- [Error Handling](#error-handling)
- [Running IO](#running-io)
- [Best Practices](#best-practices)

## Core Concepts

### What is IO?

`IO<A>` represents a computation that:
- Produces a value of type `A`
- May perform side effects (file I/O, network calls, database queries, etc.)
- Is **lazy** - side effects don't happen until `unsafeRun()` is called
- Is **referentially transparent** - can be reasoned about as a pure value

```php
use function Phunkie\Effect\Functions\io\io;

// This doesn't read the file yet - it just describes the operation
$readFile = io(fn() => file_get_contents('data.txt'));

// The file is only read when we run it
$content = $readFile->unsafeRun();  // Side effect happens here
```

### Why Use IO?

**Without IO (impure):**
```php
function getUser(int $id): User {
    $data = $db->query("SELECT * FROM users WHERE id = ?", [$id]);  // Side effect!
    return new User($data);
}

// Hard to test, hard to compose, order of execution matters
$user = getUser(1);
```

**With IO (pure):**
```php
function getUser(int $id): IO {
    return io(function() use ($id) {
        $data = $db->query("SELECT * FROM users WHERE id = ?", [$id]);
        return new User($data);
    });
}

// Easy to test (no DB needed), composable, explicit about effects
$userIO = getUser(1);  // No side effects yet
$user = $userIO->unsafeRun();  // Side effect happens here
```

## Creating IO

### From a Pure Value

```php
use function Phunkie\Effect\Functions\io\io;

// Wrap a pure value
$pure = io(42);  // IO<int>
$result = $pure->unsafeRun();  // 42
```

### From a Side Effect

```php
// Wrap a side-effecting computation
$effect = io(fn() => file_get_contents('data.txt'));  // IO<string>

// Multiple statements
$complex = io(function() {
    $data = fetchFromApi();
    $processed = processData($data);
    saveToDatabase($processed);
    return $processed;
});
```

### From Existing Values

```php
// Using the io() helper
$value = io(123);

// Direct construction
use Phunkie\Effect\IO\IO;
$io = new IO(fn() => computeValue());
```

## Transforming IO

### map - Transform the Result

```php
$readFile = io(fn() => file_get_contents('numbers.txt'));

// Transform the result
$numbers = $readFile->map(fn($content) => explode("\n", $content));
$doubled = $numbers->map(fn($nums) => array_map(fn($n) => $n * 2, $nums));

// Chain transformations
$result = $readFile
    ->map(fn($content) => explode("\n", $content))
    ->map(fn($lines) => array_map('intval', $lines))
    ->map(fn($numbers) => array_sum($numbers));
```

### flatMap - Chain Dependent Effects

```php
// Sequential effects where the second depends on the first
$program = getUser(1)
    ->flatMap(fn($user) => getUserPosts($user->id))
    ->flatMap(fn($posts) => io(fn() => json_encode($posts)));

// Real-world example
$workflow = readConfig('config.json')
    ->flatMap(fn($config) => connectToDatabase($config['db']))
    ->flatMap(fn($db) => fetchUsers($db))
    ->flatMap(fn($users) => sendEmails($users));
```

### productR (*>) - Sequence and Discard First

```php
// Run both effects, keep only the second result
$program = writeLog('Starting...')
    ->productR(performOperation())
    ->productR(writeLog('Done'));

// Equivalent to:
$program = writeLog('Starting...')
    ->flatMap(fn($_) => performOperation())
    ->flatMap(fn($result) => writeLog('Done')->map(fn($_) => $result));
```

## Combining IO

### Sequential Composition

```php
// Run effects in sequence
$sequential = io(fn() => step1())
    ->flatMap(fn($a) => io(fn() => step2($a)))
    ->flatMap(fn($b) => io(fn() => step3($b)));
```

### Parallel Execution

```php
use Phunkie\Effect\Ops\IO\ParallelOps;

$io1 = io(fn() => fetchUser(1));
$io2 = io(fn() => fetchUser(2));
$io3 = io(fn() => fetchUser(3));

// Run in parallel and combine results
$combined = $io1->parMap2($io2, fn($u1, $u2) => [$u1, $u2]);
$all = $io1->parMapN([$io2, $io3], fn($u1, $u2, $u3) => [$u1, $u2, $u3]);
```

## Async Execution

### start() - Fork to Background

The `start()` method forks a computation into a background fiber, returning immediately with a handle:

```php
// Fire and forget pattern
$sendEmail = io(fn() => mail($user->email, 'Welcome!', '...'));

$program = $sendEmail
    ->start()  // Returns IO<AsyncHandle<Unit>>
    ->map(fn($handle) => 'Email queued');  // Continue immediately

// Or await the result later
$program = $sendEmail
    ->start()
    ->flatMap(fn($handle) => {
        // Do other work while email sends
        return doOtherWork()->map(fn($result) => [
            'work' => $result,
            'email' => $handle->await()  // Wait for email
        ]);
    });
```

### Custom Execution Context

```php
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

// Use parallel threads (if ext-parallel available)
$heavyComputation = io(fn() => processLargeDataset());
$handle = $heavyComputation
    ->start(new ParallelExecutionContext())
    ->unsafeRun();

$result = $handle->await();
```

### Real-World Example

```php
// Create user and send welcome email asynchronously
function createUser(array $data): IO {
    return io(fn() => User::create($data))
        ->flatMap(fn($user) =>
            sendWelcomeEmail($user)
                ->start()  // Fork email to background
                ->map(fn($_) => $user)  // Return user immediately
        );
}

// HTTP handler
POST('/users', fn(Request $req) =>
    createUser($req->body)
        ->map(fn($user) => Created($user))  // 201 response sent while email sends
);
```

## Error Handling

### attempt() - Capture Errors as Values

```php
$risky = io(fn() => throw new \RuntimeException('Oops'));

$safe = $risky->attempt();  // IO<Validation<Throwable, A>>

$result = $safe->unsafeRun();
$result->match(
    Success: fn($value) => "Got: $value",
    Failure: fn($error) => "Error: {$error->getMessage()}"
);
```

### handleError() - Recover from Errors

```php
$risky = io(fn() => riskyOperation());

$recovered = $risky->handleError(fn($e) => 'default-value');

// Real-world example
$getUser = io(fn() => $db->findUser($id))
    ->handleError(fn($e) => null);  // Return null if not found
```

### ensure() - Fail When a Value Is Not Acceptable

`ensure` raises an error when the produced value does not satisfy a predicate. It
stays lazy — the check runs when the IO is run — and the raised error is
recoverable through `attempt()` / `handleError()`.

```php
$positive = io(fn() => readNumber())
    ->ensure(fn($n) => $n > 0, new \RuntimeException('must be positive'));

// ensureOr builds the error from the offending value
$positive = io(fn() => readNumber())
    ->ensureOr(fn($n) => $n > 0, fn($n) => new \RuntimeException("not positive: $n"));
```

`ensure` is how you express a conditional failure over an IO. A for-comprehension
**guard** — `for { $x <- io if $x > 0 } yield $x` — is deliberately not supported
for IO: a lazy `IO<A>` has no empty value to fall through to, so there is nothing
for a failed guard to become. This mirrors cats-effect, where filtering an IO is
done with `ensure`/`ensureOr` rather than with for-comprehension guard syntax. A
plain `for { $x <- io } yield ...` (no guard) works as usual, desugaring to
`flatMap`/`map`.

### Combining Error Handling

```php
$program = fetchFromApi()
    ->attempt()
    ->flatMap(fn($result) => $result->match(
        Success: fn($data) => io(fn() => processData($data)),
        Failure: fn($error) => io(fn() => logError($error))
            ->productR(io(fn() => getDefaultData()))
    ));
```

## Running IO

### unsafeRun() - Execute the Effect

```php
$io = io(fn() => 'Hello, World!');
$result = $io->unsafeRun();  // "Hello, World!"
```

⚠️ **Warning**: `unsafeRun()` performs side effects. Only call it at the "edge of the world" (application entry point, IOApp, HTTP handlers).

### unsafeRunSync() - Execute and Await Async

```php
$async = io(fn() => slowOperation())->start();
$handle = $async->unsafeRun();  // Returns AsyncHandle

// Or use unsafeRunSync to automatically await
$result = $async->unsafeRunSync();  // Blocks until complete
```

### In IOApp

```php
use Phunkie\Effect\IO\IOApp;

class MyApp extends IOApp {
    public function run(): IO {
        return processData()
            ->flatMap(fn($data) => saveResults($data))
            ->map(fn($_) => 0);  // Exit code
    }
}

// IOApp handles unsafeRun() for you
```

## Best Practices

### 1. Keep IO at the Boundaries

```php
// ❌ Bad - IO in the middle of pure logic
function calculateTotal(array $items): IO {
    return io(fn() => array_sum(array_map(fn($i) => $i->price, $items)));
}

// ✅ Good - Pure logic, IO at boundaries
function calculateTotal(array $items): float {
    return array_sum(array_map(fn($i) => $i->price, $items));
}

function getTotalFromDb(int $orderId): IO {
    return fetchOrder($orderId)  // IO
        ->map(fn($order) => calculateTotal($order->items));  // Pure
}
```

### 2. Use flatMap for Dependent Effects

```php
// ❌ Bad - Nested unsafeRun
$user = getUser($id)->unsafeRun();
$posts = getUserPosts($user->id)->unsafeRun();

// ✅ Good - Composed with flatMap
$program = getUser($id)
    ->flatMap(fn($user) => getUserPosts($user->id));
```

### 3. Avoid Mixing Pure and Impure

```php
// ❌ Bad - Side effect hidden in pure function
function processUser(User $user): User {
    logToFile("Processing {$user->name}");  // Hidden side effect!
    return $user;
}

// ✅ Good - Explicit about effects
function processUser(User $user): IO {
    return io(fn() => logToFile("Processing {$user->name}"))
        ->map(fn($_) => $user);
}
```

### 4. Use start() for Independent Effects

```php
// ❌ Bad - Sequential when parallel is possible
$program = sendEmail($user)
    ->flatMap(fn($_) => sendSms($user))
    ->flatMap(fn($_) => logActivity($user));

// ✅ Good - Parallel independent effects
$program = sendEmail($user)->start()
    ->flatMap(fn($emailHandle) => sendSms($user)->start())
    ->flatMap(fn($smsHandle) => logActivity($user)->start())
    ->map(fn($logHandle) => 'All notifications queued');
```

### 5. Name Your Effects Descriptively

```php
// ❌ Bad
$io1 = io(fn() => doStuff());
$io2 = $io1->flatMap(fn($x) => io(fn() => doMore($x)));

// ✅ Good
$fetchUser = io(fn() => $db->getUser($id));
$enrichWithPosts = fn($user) => io(fn() => $db->getPosts($user->id))
    ->map(fn($posts) => [...$user, 'posts' => $posts]);

$program = $fetchUser->flatMap($enrichWithPosts);
```

## See Also

- [IOApp](ioapp.md) - Application entry point
- [Error Handling](error-handling.md) - Advanced error handling patterns
- [Concurrency](concurrency.md) - Parallel and async execution
- [Resource Management](resources.md) - Safe resource handling

# Understanding Blocking Operations

## What is a Blocker?

A Blocker is a mechanism to handle potentially blocking operations in a controlled way. It provides a safe context for executing operations that might block the current execution context, such as:

- File I/O operations
- Database queries
- Network requests
- Long-running computations

```php
use function Phunkie\Effect\Functions\blocking\blocking;

// Reading a file with a blocker
$readFile = blocking(function() {
    return file_get_contents('large-file.txt');
});

// Database query with a blocker
$query = blocking(function() {
    return $db->query('SELECT * FROM large_table');
});

// Network request with a blocker
$request = blocking(function() {
    return $httpClient->get('https://api.example.com/data');
});
```

## Execution Contexts

When working with blocking operations, you can control how they execute using different execution contexts. By default, Phunkie uses PHP's Fiber-based execution, but you can choose other contexts based on your needs.

### Basic Usage

The simplest way to use blocking operations is to let Phunkie handle the execution context:

```php
// Uses the default Fiber-based execution
$result = blocking(function() {
    return expensiveOperation();
});
```

The `blocking` function returns an `IO` value, which means you can control when and how the operation executes:

```php
// Get an IO value
$io = blocking(function() {
    return expensiveOperation();
});

// Execute in the current context (synchronous)
$result = $io->unsafeRunSync();

// Get a handle for asynchronous execution
$handle = $io->unsafeRun();  // Returns an AsyncHandle
// The operation hasn't started yet - it's lazy

// When you're ready to execute
$result = $handle->await();  // This triggers the execution context
```

This gives you flexibility in how you handle blocking operations:
- Use `unsafeRunSync` when you want to execute in the current context and get the result immediately
- Use `unsafeRun` when you want to defer execution until you're ready to handle it
- Chain operations before executing them

For example, you can prepare multiple operations and execute them when needed:
```php
$io1 = blocking(function() { return operation1(); });
$io2 = blocking(function() { return operation2(); });

$handle1 = $io1->unsafeRun();
$handle2 = $io2->unsafeRun();

// Operations haven't started yet - they're lazy
// Do other work...

// When you're ready to execute
$result1 = $handle1->await();  // Triggers execution of operation1
$result2 = $handle2->await();  // Triggers execution of operation2
```

### Custom Execution Contexts

For specific use cases, you can specify a different execution context:

```php
// Using a thread pool for CPU-intensive tasks
$result = blocking(function() {
    return cpuIntensiveOperation();
}, new ThreadPoolExecutionContext(8));

// Using async execution for I/O-bound tasks
$result = blocking(function() {
    return makeHttpRequest();
}, new AsyncExecutionContext());
```

### Configuring the Default Context

You can change the default execution context for all blocking operations in your application:

```php
// Set async execution as the default
Runtime::setDefaultContext(new AsyncExecutionContext());

// All subsequent blocking calls will use async execution
$result = blocking(function() {
    return expensiveOperation();
});
```

### When to Use Different Contexts

1. **Default Fiber Context**
   - Good for most blocking operations
   - Lightweight and built into PHP
   - Suitable for I/O operations and moderate computations

2. **Thread Pool Context**
   - Best for CPU-intensive tasks
   - When you need parallel processing
   - For operations that can benefit from multiple cores

3. **Async Context**
   - Ideal for I/O-bound operations
   - When handling many concurrent connections
   - For operations that spend most time waiting

For detailed examples and advanced usage patterns, see the [Cookbook](cookbook.md) section.

## When to Use Blockers

In traditional PHP applications, blocking operations are often taken for granted. When you read a file, query a database, or make an HTTP request, these operations block the entire execution context until they complete. This is like having a single counter in a kitchen where everything happens - when someone is slowly chopping vegetables, everyone else has to wait.

Consider this common scenario:
```php
// Traditional PHP code
$data = file_get_contents('large-file.txt');  // Blocks here
$processed = processData($data);              // Can't start until file is read
$result = saveToDatabase($processed);         // Can't start until processing is done
```

This approach has several problems:
1. Each blocking operation holds up the entire application
2. Resources are tied up waiting for slow operations
3. The application can't handle other requests during these waits
4. Error handling becomes more complex

Blockers solve these problems by providing a dedicated "slow cooking area" in your application's kitchen. Just as a restaurant kitchen has separate stations for different tasks, blockers create a controlled environment for slow operations:

```php
// With blockers
$data = blocking(function() {
    return file_get_contents('large-file.txt');
});

$processed = $data->map(function($content) {
    return processData($content);
});

$result = $processed->flatMap(function($data) {
    return blocking(function() use ($data) {
        return saveToDatabase($data);
    });
});
```

This approach offers several benefits:
1. The main application flow continues while slow operations run
2. Resources are managed efficiently
3. The application remains responsive
4. Error handling is more structured

Think of blockers as a dedicated slow-cooking area in a kitchen. While the main counter handles quick tasks like plating and garnishing, the slow-cooking area manages time-consuming tasks like braising and simmering. This separation allows the kitchen to be more efficient and responsive, just as blockers help your application handle both quick and slow operations effectively.

## Working with Blockers

### Basic Usage

Just as a kitchen has different stations for different tasks, blockers provide different ways to handle operations:

```php
// Simple blocking operation
$result = blocking(function() {
    return expensiveComputation();
})->unsafeRun();

// Chaining blocking operations
$pipeline = blocking(function() {
    return step1();
})->flatMap(function($result) {
    return blocking(function() use ($result) {
        return step2($result);
    });
})->flatMap(function($result) {
    return blocking(function() use ($result) {
        return step3($result);
    });
});
```

### Resource Management

Like a kitchen's mise en place, proper resource management is crucial. The `bracket` pattern ensures resources are properly acquired, used, and released:

```php
// Using bracket for resource management
$process = bracket(
    blocking(function() {
        return openResource();
    }),
    function($resource) {
        return blocking(function() use ($resource) {
            return useResource($resource);
        });
    },
    function($resource) {
        return blocking(function() use ($resource) {
            return closeResource($resource);
        });
    }
);
```

### Error Handling

Just as a kitchen needs to handle unexpected situations, blockers provide robust error handling:

```php
// Handling errors in blocking operations
$safeOperation = blocking(function() {
    return riskyOperation();
})->handleError(function($error) {
    return handleError($error);
})->attempt();

// Retrying failed operations
$withRetry = blocking(function() {
    return unreliableOperation();
})->retry(3, 1000); // 3 retries with 1 second delay
```

## Best Practices

1. **Isolate Blocking Operations**
   ```php
   // Good: Isolated blocking operation
   $result = blocking(function() {
       return expensiveOperation();
   });

   // Bad: Mixing blocking and non-blocking
   $result = expensiveOperation(); // Direct blocking call
   ```

2. **Use Resource Management**
   ```php
   // Good: Using bracket for resource management
   $result = bracket(
       blocking(function() { return openResource(); }),
       function($r) { return useResource($r); },
       function($r) { return closeResource($r); }
   );

   // Bad: Manual resource management
   $resource = openResource();
   try {
       $result = useResource($resource);
   } finally {
       closeResource($resource);
   }
   ```

3. **Handle Errors Appropriately**
   ```php
   // Good: Proper error handling
   $result = blocking(function() {
       return riskyOperation();
   })->handleError(function($error) {
       return fallbackOperation();
   });

   // Bad: Ignoring errors
   $result = blocking(function() {
       return riskyOperation();
   });
   ```

4. **Control Concurrency**
   ```php
   // Good: Controlled concurrency
   $results = sequence(array_map(function($item) {
       return blocking(function() use ($item) {
           return processItem($item);
       });
   }, $items));

   // Bad: Uncontrolled concurrency
   $results = array_map(function($item) {
       return processItem($item);
   }, $items);
   ```

Remember that while blockers provide a safe way to handle blocking operations, they should be used judiciously. Always consider whether an operation truly needs to be blocking and if there are non-blocking alternatives available. Just as a kitchen needs to balance between quick service and slow-cooked dishes, your application needs to balance between blocking and non-blocking operations.

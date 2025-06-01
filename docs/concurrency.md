# Concurrency

Concurrency in Phunkie Effects is built around the concept of execution contexts and blockers. This chapter covers how to handle concurrent operations in a controlled and safe way.

## Blockers

A Blocker is a mechanism to handle potentially blocking operations in a controlled way. It provides a safe context for executing operations that might block the current execution context.

Think of blockers as a dedicated slow-cooking area in a kitchen. While the main counter handles quick tasks like plating and garnishing, the slow-cooking area manages time-consuming tasks like braising and simmering. This separation allows the kitchen to be more efficient and responsive, just as blockers help your application handle both quick and slow operations effectively.

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

Blockers solve these problems by providing a dedicated "slow cooking area" in your application's kitchen:

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

Blockers are essential for:
- File I/O operations
- Database queries
- Network requests
- Long-running computations
- Any operation that might block the current execution context

## Execution Context

Execution contexts determine how operations are executed. Phunkie provides several execution contexts:

### Fiber Context
```php
use Phunkie\Effect\Concurrent\FiberExecutionContext;

$context = new FiberExecutionContext();
$result = $context->execute(function() { return 42; });
```

### Parallel Context
```php
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

$context = new ParallelExecutionContext();
$handle = $context->executeAsync(function() { return 42; });
$result = $handle->await();
```

Each context has its own characteristics:
- **Fiber Context**: Lightweight, cooperative multitasking
- **Parallel Context**: True parallel execution using PHP's parallel extension
- **Process Context**: Isolated execution in separate processes

## Parallel Execution

Parallel execution allows you to run operations concurrently:

```php
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

$operation1 = io(function() {
    return blocking(function() {
        sleep(1);
        return 1;
    });
});

$operation2 = io(function() {
    return blocking(function() {
        sleep(1);
        return 2;
    });
});

$result = $operation1->parMap2($operation2, fn($a, $b) => $a + $b)->unsafeRun();
// Total execution time will be ~1 second, not 2 seconds
```

### ZTS and PHP Parallelism

> **Important**: Parallel execution in PHP requires ZTS (Zend Thread Safety) enabled. You can check if your PHP installation has ZTS enabled by running:
> ```php
> if (PHP_ZTS) {
>     echo "ZTS is enabled";
> } else {
>     echo "ZTS is not enabled";
> }
> ```

#### Parallelism Limitations

When using parallel execution, be aware of these important limitations:

1. **Memory Isolation**
   - Each parallel process has its own memory space
   - No shared memory between processes
   - No access to parent process variables

2. **Resource Restrictions**
   - Cannot share file handles
   - Cannot share database connections
   - Cannot share network connections
   - Cannot share any resource that requires cleanup

3. **Class and Function Limitations**
   - Cannot use classes defined in the parent process
   - Cannot use functions defined in the parent process
   - Cannot use closures that capture variables from the parent process
   - Cannot use objects created in the parent process

4. **Safe Operations**
   ```php
   // Safe: Basic types and operations
   $result = parallel(function() {
       $a = 1;
       $b = 2;
       return $a + $b;
   });

   // Safe: Built-in PHP functions
   $result = parallel(function() {
       return strtoupper("hello");
   });

   // UNSAFE: Using parent process objects
   $obj = new MyClass();
   $result = parallel(function() use ($obj) {  // This will fail
       return $obj->method();
   });

   // UNSAFE: Using parent process functions
   function parentFunc() { return 42; }
   $result = parallel(function() {
       return parentFunc();  // This will fail
   });
   ```

5. **Performance Considerations**
   - Process creation has overhead
   - IPC (Inter-Process Communication) has overhead
   - Not suitable for very small operations
   - Best for CPU-intensive tasks that take significant time

6. **Error Handling**
   ```php
   try {
       $result = parallel(function() {
           // Your parallel code here
       });
   } catch (ParallelException $e) {
       // Handle parallel execution errors
   }
   ```

7. **Resource Management**
   ```php
   // Good: Create resources inside parallel process
   $result = parallel(function() {
       $db = new PDO("mysql:host=localhost;dbname=test", "user", "pass");
       return $db->query("SELECT * FROM users")->fetchAll();
   });

   // Bad: Using resources from parent process
   $db = new PDO("mysql:host=localhost;dbname=test", "user", "pass");
   $result = parallel(function() use ($db) {  // This will fail
       return $db->query("SELECT * FROM users")->fetchAll();
   });
   ```

Remember that parallel execution is not a silver bullet. Consider:
- The overhead of process creation
- The limitations of process isolation
- The complexity of error handling
- The need for proper resource management

When in doubt, prefer simpler concurrency models like fibers for I/O operations and only use parallel execution for CPU-intensive tasks that benefit from true parallelism.

## Sequence and Traverse

These combinators help you work with collections of effects:

### Sequence
```php
use function Phunkie\Effect\Functions\io\io;

$ios = [
    io(function() { return 1; }),
    io(function() { return 2; }),
    io(function() { return 3; })
];

$result = sequence($ios)->unsafeRun(); // [1, 2, 3]
```

### Traverse
```php
use function Phunkie\Effect\Functions\io\io;

$numbers = [1, 2, 3];

$result = traverse($numbers, fn($n) => 
    io(function() use ($n) { 
        return $n * 2; 
    })
)->unsafeRun(); // [2, 4, 6]
```

## Race, Both, Either

These combinators help you handle concurrent operations in different ways:

### Race
```php
$fast = io(function() {
    return blocking(function() {
        usleep(100000); // 0.1 seconds
        return "fast";
    });
});

$slow = io(function() {
    return blocking(function() {
        usleep(200000); // 0.2 seconds
        return "slow";
    });
});

$result = $fast->race($slow)->unsafeRun(); // "fast"
```

### Both
```php
$result = $operation1->both($operation2)->unsafeRun();
// Returns [result1, result2]
```

### Either
```php
$result = $operation1->either($operation2)->unsafeRun();
// Returns Left(result1) or Right(result2)
```

## Cancellation

Cancellation allows you to stop operations that are no longer needed:

```php
$operation = io(function() {
    return blocking(function() {
        sleep(10);
        return "done";
    });
});

$handle = $operation->unsafeRun();
// ... some time later ...
$handle->cancel();
```

## Delay

Delay allows you to schedule operations to run after a specified time:

```php
$operation = io(function() {
    return blocking(function() {
        return "delayed";
    });
});

$result = $operation->delay(1000)->unsafeRun(); // Runs after 1 second
```

## Channels

Channels provide a way to communicate between concurrent operations:

```php
$channel = new Channel();

$producer = io(function() use ($channel) {
    return blocking(function() use ($channel) {
        $channel->send("message");
    });
});

$consumer = io(function() use ($channel) {
    return blocking(function() use ($channel) {
        return $channel->receive();
    });
});

$result = $producer->both($consumer)->unsafeRun();
```

## Best Practices

1. **Choose the Right Execution Context**
   - Use Fiber context for I/O operations
   - Use Parallel context for CPU-intensive tasks
   - Use Process context for isolated operations

2. **Handle Resources Properly**
   ```php
   $result = bracket(
       blocking(function() { return openResource(); }),
       function($r) { return useResource($r); },
       function($r) { return closeResource($r); }
   );
   ```

3. **Use Parallel Execution Wisely**
   ```php
   // Good: Parallel execution for independent operations
   $result = $operation1->parMap2($operation2, fn($a, $b) => $a + $b);

   // Bad: Parallel execution for dependent operations
   $result = $operation1->flatMap(fn($a) => $operation2->map(fn($b) => $a + $b));
   ```

4. **Handle Cancellation**
   ```php
   $handle = $operation->unsafeRun();
   try {
       $result = $handle->await();
   } catch (CancellationException $e) {
       // Handle cancellation
   }
   ```

5. **Use Error Handling**
   ```php
   $result = $operation
       ->handleError(fn($e) => "fallback")
       ->timeout(1000)
       ->unsafeRun();
   ```

Remember that concurrency is a powerful tool, but it comes with complexity. Always consider:
- Resource management
- Error handling
- Cancellation
- Timeouts
- State management

The key is to use the right tool for the job and handle all edge cases appropriately. 
# Resources

Resource management is a critical aspect of any application. This chapter covers how to safely acquire, use, and release resources using brackets and resource combinators.

## Brackets

A bracket is a pattern that ensures resources are properly acquired and released, even in the presence of errors. It consists of three parts:

1. **Acquire**: Get the resource
2. **Use**: Use the resource
3. **Release**: Clean up the resource

```php
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

$result = bracket(
    // Acquire
    blocking(function() { 
        return fopen('file.txt', 'r'); 
    }),
    // Use
    function($handle) {
        return blocking(function() use ($handle) {
            return fread($handle, 1024);
        });
    },
    // Release
    function($handle) {
        return blocking(function() use ($handle) {
            fclose($handle);
        });
    }
)->unsafeRun();
```

## Resource Combinators

### Bracket

The `bracket` combinator ensures proper resource cleanup:

```php
$result = bracket(
    blocking(function() { return openDatabase(); }),
    function($db) { return queryDatabase($db); },
    function($db) { return closeDatabase($db); }
)->unsafeRun();
```

### BracketCase

`bracketCase` tells the release step how the use step ended, as an `ExitCase`: `Completed`, or `Error` when it threw. The error is rethrown once the release step has run.

```php
use Phunkie\Effect\IO\ExitCase;

use function Phunkie\Effect\Functions\io\bracketCase;

$result = bracketCase(
    blocking(function() { return openFile(); }),
    function($file) { return readFile($file); },
    function($file, ExitCase $exitCase) {
        return match($exitCase) {
            ExitCase::Completed => closeFile($file),
            ExitCase::Error => closeAndLogError($file),
            ExitCase::Cancelled => closeAndNotify($file),
        };
    }
)->unsafeRun();
```

`ExitCase::Cancelled` exists for the day cancellation reaches brackets; nothing produces it yet.

### BracketE

`bracketE` hands the release step the error the use step raised, or `null` when it completed:

```php
use function Phunkie\Effect\Functions\io\bracketE;

$result = bracketE(
    blocking(function() { return openConnection(); }),
    function($conn) { return useConnection($conn); },
    function($conn, ?Throwable $error) {
        return null === $error ? closeConnection($conn) : closeAndLogError($conn, $error);
    }
)->unsafeRun();
```

`bracket` is `bracketE` with a release step that ignores the error.

## Resource

A `Resource` is a bracket as a value: something acquired for the duration of a use step and released after it, however the step ends. Resources compose before anything is acquired, and `use()` turns the whole thing back into one `IO`:

```php
use Phunkie\Effect\IO\Resource;

$connection = Resource::make(
    io(fn() => connect($dsn)),
    fn($conn) => io(fn() => $conn->close()),
);

$statement = $connection->flatMap(fn($conn) => Resource::make(
    io(fn() => $conn->prepare($sql)),
    fn($stmt) => io(fn() => $stmt->closeCursor()),
));

$rows = $statement->use(fn($stmt) => io(fn() => $stmt->fetchAll()))->unsafeRun();
```

`flatMap` nests: the statement is acquired inside the connection and released before it. `map` transforms what was acquired; `Resource::pure($value)` and `Resource::eval($io)` lift a value or an effect that needs no releasing, so they compose with the ones that do.

## Common Resource Patterns

### File Operations

```php
$result = bracket(
    blocking(function() { return fopen('data.txt', 'r'); }),
    function($file) {
        return blocking(function() use ($file) {
            return fread($file, 1024);
        });
    },
    function($file) {
        return blocking(function() use ($file) {
            fclose($file);
        });
    }
)->unsafeRun();
```

### Database Connections

```php
$result = bracket(
    blocking(function() { return $db->connect(); }),
    function($conn) {
        return blocking(function() use ($conn) {
            return $conn->query('SELECT * FROM users');
        });
    },
    function($conn) {
        return blocking(function() use ($conn) {
            $conn->close();
        });
    }
)->unsafeRun();
```

### Network Connections

```php
$result = bracket(
    blocking(function() { return $client->connect(); }),
    function($conn) {
        return blocking(function() use ($conn) {
            return $conn->request('GET', '/api/data');
        });
    },
    function($conn) {
        return blocking(function() use ($conn) {
            $conn->disconnect();
        });
    }
)->unsafeRun();
```

## Best Practices

1. **Always Use Brackets for Resources**
   ```php
   // Good: Using bracket
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

2. **Compose Resource Operations**
   ```php
   $result = bracket(
       blocking(function() { return openResource(); }),
       function($r) {
           return bracket(
               blocking(function() { return openSubResource($r); }),
               function($sr) { return useSubResource($sr); },
               function($sr) { return closeSubResource($sr); }
           );
       },
       function($r) { return closeResource($r); }
   );
   ```

3. **Handle Resource Limits**
   ```php
   $result = bracket(
       blocking(function() { return openResource(); }),
       function($r) {
           return $operation
               ->handleError(fn($e) => "fallback");
       },
       function($r) { return closeResource($r); }
   );
   ```

Remember that proper resource management is crucial for:
- Preventing resource leaks
- Ensuring cleanup in error cases
- Managing resource limits
- Handling concurrent access
- Maintaining system stability

The key is to use the appropriate bracket combinator for your use case and always ensure proper cleanup. 
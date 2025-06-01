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

`bracketCase` allows different cleanup strategies based on how the resource was used:

```php
$result = bracketCase(
    blocking(function() { return openFile(); }),
    function($file) { return readFile($file); },
    function($file, $exitCase) {
        return match($exitCase) {
            ExitCase::Completed => closeFile($file),
            ExitCase::Error => closeAndLogError($file),
            ExitCase::Cancelled => closeAndNotify($file)
        };
    }
)->unsafeRun();
```

### BracketE

`bracketE` combines error handling with resource management:

```php
$result = bracketE(
    blocking(function() { return openConnection(); }),
    function($conn) { return useConnection($conn); },
    function($conn, $error) {
        return match($error) {
            null => closeConnection($conn),
            default => closeAndLogError($conn, $error)
        };
    }
)->unsafeRun();
```

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

2. **Handle Errors in Release**
   ```php
   $result = bracketE(
       blocking(function() { return openResource(); }),
       function($r) { return useResource($r); },
       function($r, $error) {
           return match($error) {
               null => closeResource($r),
               default => closeAndLogError($r, $error)
           };
       }
   );
   ```

3. **Use Appropriate Cleanup Strategies**
   ```php
   $result = bracketCase(
       blocking(function() { return openResource(); }),
       function($r) { return useResource($r); },
       function($r, $exitCase) {
           return match($exitCase) {
               ExitCase::Completed => closeResource($r),
               ExitCase::Error => closeAndLogError($r),
               ExitCase::Cancelled => closeAndNotify($r)
           };
       }
   );
   ```

4. **Compose Resource Operations**
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

5. **Handle Resource Limits**
   ```php
   $result = bracket(
       blocking(function() { return openResource(); }),
       function($r) {
           return $operation
               ->timeout(1000)
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
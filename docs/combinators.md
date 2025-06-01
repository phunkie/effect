# Combinators

Combinators are higher-order functions that combine or transform effects in various ways. They provide a powerful way to compose and manipulate effects in a functional style.

## Map and Filter

The most basic combinators are `map` and `filter`. They allow you to transform and filter values within effects.

### Map

`map` transforms the value inside an effect using a function:

```php
use function Phunkie\Effect\Functions\io\io;

$io = io(function() { return 42; });

$doubled = $io->map(fn($x) => $x * 2);
$result = $doubled->unsafeRun(); // 84
```

### Filter

`filter` creates a new effect that only contains values that satisfy a predicate:

```php
use function Phunkie\Effect\Functions\io\io;

$io = io(function() { return 42; });

$filtered = $io->filter(fn($x) => $x > 40);
$result = $filtered->unsafeRun(); // 42

$empty = $io->filter(fn($x) => $x < 40);
$result = $empty->unsafeRun(); // throws NoSuchElementException
```

## Sequence and Traverse

These combinators help you work with collections of effects.

### Sequence

`sequence` transforms a collection of effects into an effect of a collection:

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

`traverse` is like `sequence` but allows you to transform each value before sequencing:

```php
use function Phunkie\Effect\Functions\io\io;

$numbers = [1, 2, 3];

$result = traverse($numbers, fn($n) => 
    io(function() use ($n) { 
        return $n * 2; 
    })
)->unsafeRun(); // [2, 4, 6]
```

## Race and Timeout

These combinators help you handle concurrent operations and timeouts.

### Race

`race` runs two effects concurrently and returns the result of the first one to complete:

```php
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

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

### Timeout

`timeout` adds a timeout to an effect, failing if it takes too long:

```php
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\blocking\blocking;

$slow = io(function() {
    return blocking(function() {
        sleep(2);
        return "done";
    });
});

try {
    $result = $slow->timeout(1000)->unsafeRun(); // throws TimeoutException
} catch (\Exception $e) {
    // Handle timeout
}
```

## Error Handling

Combinators also provide ways to handle errors in effects.

### HandleError

`handleError` allows you to recover from errors:

```php
use function Phunkie\Effect\Functions\io\io;

$risky = io(function() {
    throw new \Exception("Something went wrong");
});

$safe = $risky->handleError(function($error) {
    return "Recovered from: " . $error->getMessage();
});

$result = $safe->unsafeRun(); // "Recovered from: Something went wrong"
```

### Attempt

`attempt` wraps the result in an `Either` type to handle success and failure:

```php
use function Phunkie\Effect\Functions\io\io;

$risky = io(function() {
    throw new \Exception("Something went wrong");
});

$result = $risky->attempt()->unsafeRun();
// Returns Left(Exception) instead of throwing
```

## Best Practices

1. **Use Map for Transformations**
   ```php
   // Good: Using map for transformations
   $result = $io->map(fn($x) => $x * 2);

   // Bad: Direct transformation
   $value = $io->unsafeRun();
   $result = $value * 2;
   ```

2. **Use Sequence for Collections**
   ```php
   // Good: Using sequence for collections
   $results = sequence($ios)->unsafeRun();

   // Bad: Manual sequencing
   $results = [];
   foreach ($ios as $io) {
       $results[] = $io->unsafeRun();
   }
   ```

3. **Use Race for Concurrent Operations**
   ```php
   // Good: Using race for concurrent operations
   $result = $operation1->race($operation2)->unsafeRun();

   // Bad: Manual concurrent execution
   $handle1 = $operation1->unsafeRun();
   $handle2 = $operation2->unsafeRun();
   $result = $handle1->await(); // Might wait unnecessarily
   ```

4. **Use Timeout for Long-Running Operations**
   ```php
   // Good: Using timeout for long-running operations
   $result = $operation->timeout(1000)->unsafeRun();

   // Bad: No timeout
   $result = $operation->unsafeRun(); // Might hang indefinitely
   ```

5. **Use Error Handling Combinators**
   ```php
   // Good: Using error handling combinators
   $result = $risky->handleError(fn($e) => "fallback")->unsafeRun();

   // Bad: Try-catch blocks
   try {
       $result = $risky->unsafeRun();
   } catch (\Exception $e) {
       $result = "fallback";
   }
   ```

Remember that combinators are designed to work together. You can chain them to create complex effect compositions:

```php
$result = $io
    ->map(fn($x) => $x * 2)
    ->filter(fn($x) => $x > 10)
    ->timeout(1000)
    ->handleError(fn($e) => 0)
    ->unsafeRun();
```

This composition:
1. Doubles the value
2. Filters out values less than 10
3. Times out after 1 second
4. Returns 0 if any error occurs
5. Executes the entire chain

The power of combinators lies in their ability to compose effects in a declarative way, making your code more readable and maintainable. 
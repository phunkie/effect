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

Both live in `Phunkie\Effect\Functions\io`, next to `io` and `bracket`.

### Sequence

`sequence` turns a list of effects into one effect of their results, run in order when the effect runs, keys kept:

```php
use function Phunkie\Effect\Functions\io\{io, sequence};

$ios = [io(fn() => 1), io(fn() => 2), io(fn() => 3)];

$result = sequence($ios)->unsafeRun(); // [1, 2, 3]
```

A blocking effect in the list is awaited, so `sequence` yields values, never handles.

### Traverse

`traverse` maps each value to an effect and sequences the results:

```php
use function Phunkie\Effect\Functions\io\traverse;

$result = traverse([1, 2, 3], fn($n) => io(fn() => $n * 2))->unsafeRun(); // [2, 4, 6]
```

## Race and Timeout

`race`, `either` and `timeout` run an effect where it can be polled and cancelled. A blocking effect runs in the context it was given; any other effect runs in the context passed as the last argument, `ParallelExecutionContext` by default, which needs `ext-parallel`. A context whose handles cannot be polled, such as the fiber context, is refused with a `RuntimeException` that says so.

### Race

`race` returns the result of whichever effect finishes first and cancels the other:

```php
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

use function Phunkie\Effect\Functions\blocking\blocking;

$fast = blocking(function() {
    usleep(100000);

    return "fast";
}, new ParallelExecutionContext());

$slow = blocking(function() {
    usleep(200000);

    return "slow";
}, new ParallelExecutionContext());

$result = $fast->race($slow)->unsafeRun(); // "fast"
```

`either` is the same race that says who won: `Left` for the effect it was called on, `Right` for the other.

### Timeout

`timeout` gives an effect a number of milliseconds; when they run out the effect is cancelled and a `Phunkie\Effect\Concurrent\TimeoutException` is raised:

```php
use Phunkie\Effect\Concurrent\TimeoutException;

try {
    $result = $slow->timeout(1000)->unsafeRun();
} catch (TimeoutException $e) {
    // Timed out after 1000 ms.
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

### HandleErrorWith

`handleErrorWith` recovers with another effect, which runs in place of the failed one:

```php
use function Phunkie\Effect\Functions\io\io;

$content = io(fn() => readCache($key))
    ->handleErrorWith(fn($error) => io(fn() => readOrigin($key)));
```

### Recover

`recover` handles one exception class and lets every other error propagate:

```php
use function Phunkie\Effect\Functions\io\io;

$user = io(fn() => $users->get($id))
    ->recover(UserNotFound::class, fn(UserNotFound $e) => io(fn() => null));

$user->unsafeRun();   // null when the user is missing, still throws on anything else
```

### Attempt

`attempt` wraps the outcome in a `Validation`, `Success` or `Failure`, instead of throwing:

```php
use function Phunkie\Effect\Functions\io\io;

$risky = io(function() {
    throw new \Exception("Something went wrong");
});

$result = $risky->attempt()->unsafeRun();   // Failure(Exception)
$message = $result->fold(fn($error) => $error->getMessage())(fn($value) => "ok: $value");
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


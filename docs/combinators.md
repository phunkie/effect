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


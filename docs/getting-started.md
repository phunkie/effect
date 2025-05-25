# Getting Started with Phunkie Effects

Phunkie Effects is a functional effects library for PHP that helps you manage side effects in a purely functional way. This guide will walk you through the basics of using Phunkie Effects.

## Installation

```bash
composer require phunkie/effect
```

## Basic Usage

### Creating an IO

The most basic way to create an IO is using the `io` function:

```php
use function Phunkie\Effect\Functions\io\io;

// Create an IO from a pure value
$pure = io(42);

// Create an IO from a side effect
$effect = io(function() {
    return file_get_contents('data.txt');
});
```

### Running an IO

To execute an IO and get its result, use the `unsafeRun` method:

```php
$result = $effect->unsafeRun();
```

### Mapping Over IO

You can transform the value inside an IO using `map`:

```php
$uppercase = $effect->map(function($content) {
    return strtoupper($content);
});

// The content will be uppercase when run
$result = $uppercase->unsafeRun();
```

### Error Handling

IO provides error handling through the `handleError` method:

```php
$safe = $effect->handleError(function($error) {
    return "Error reading file: " . $error->getMessage();
});

// If the file read fails, we'll get the error message
$result = $safe->unsafeRun();
```

### Creating an Application

To create a full application, extend the `IOApp` class:

```php
use Phunkie\Effect\IO\IOApp;
use function Phunkie\Effect\Functions\io\io;
use const Phunkie\Effect\IOApp\ExitSuccess;

class MyApp extends IOApp
{
    public function run(): IO
    {
        return io(function() {
            echo "Hello, Effects!";
            return ExitSuccess;
        });
    }
}
```

Run your application using the Phunkie console:

```bash
$ bin/phunkie MyApp
Hello, Effects!
```

## Common Patterns

### Resource Management

Use the `bracket` pattern to safely manage resources:

```php
$file = io(function() {
    return fopen('data.txt', 'r');
});

$content = $file->bracket(
    function($handle) {
        return fread($handle, filesize('data.txt'));
    },
    function($handle) {
        fclose($handle);
    }
);
```

### Composition

Break down complex operations into smaller, composable IOs:

```php
$readConfig = io(function() {
    return json_decode(file_get_contents('config.json'), true);
});

$connectDb = function($config) {
    return io(function() use ($config) {
        return new Database($config);
    });
};

$app = $readConfig->flatMap($connectDb);
```

### Parallel Execution

Run multiple IOs in parallel:

```php
$users = io(function() {
    return $db->query("SELECT * FROM users");
});

$posts = io(function() {
    return $db->query("SELECT * FROM posts");
});

$result = $users->zipWith($posts);
```

## Best Practices

1. **Keep it Pure**: Wrap all side effects in IO
2. **Error Handling**: Always handle potential errors
3. **Resource Management**: Use bracket for resources
4. **Composition**: Break down complex operations
5. **Type Safety**: Use proper type hints and docblocks

## Next Steps

- Read about [IO App and Console](io-app.md)
- Learn about [Blocking Operations](blocker.md)
- Explore [Combinators](combinators.md)
- Understand [Concurrency](concurrency.md) 
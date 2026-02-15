# Phunkie Effect

[![CI](https://github.com/phunkie/effect/actions/workflows/ci.yml/badge.svg)](https://github.com/phunkie/effect/actions)
[![Latest Version](https://img.shields.io/packagist/v/phunkie/effect.svg?style=flat-square)](https://packagist.org/packages/phunkie/effect)
[![Total Downloads](https://poser.pugx.org/phunkie/effect/downloads)](https://packagist.org/packages/phunkie/effect)
[![License](https://img.shields.io/packagist/l/phunkie/effect.svg?style=flat-square)](https://github.com/phunkie/effect/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/phunkie/effect.svg?style=flat-square)](https://packagist.org/packages/phunkie/effect)

A functional effects library for PHP inspired by Scala's cats-effect. Phunkie Effect provides a way to manage side effects in a purely functional way, making your code more predictable, testable, and maintainable.

## Installation

```bash
composer require phunkie/effect
```

## Requirements

- PHP 8.2 or higher

## Basic Usage

### IO Monad

The `IO` monad is the primary way to handle effects in Phunkie Effects. It allows you to wrap side effects in a pure functional context. Following Phunkie's design philosophy, we use traits to extend types and functions to construct and manipulate them.

```php
use Phunkie\Effect\Functions\io\io;
use Phunkie\Effect\IO\IO;

// Create an IO from a pure value using the io function
$pure = io(42);

// Create an IO from a side effect
$effect = io(function() {
    return file_get_contents('data.txt');
});

// Map over an IO using the FunctorOps trait
$mapped = $effect->map(function($content) {
    return strtoupper($content);
});

// Chain IOs using the MonadOps trait
$chained = $effect->flatMap(function($content) {
    return io(function() use ($content) {
        return file_put_contents('output.txt', $content);
    });
});
```

### Async Execution with start()

The `start()` method allows you to fork computations into background fibers, enabling fire-and-forget patterns:

```php
use function Phunkie\Effect\Functions\io\io;

// Define an async operation
$sendEmail = io(function() use ($user) {
    mail($user->email, 'Welcome!', '...');
    return 'sent';
});

// Fork to background and continue immediately
$program = $sendEmail
    ->start()  // Returns IO<AsyncHandle<string>>
    ->map(function($handle) {
        // Continue with other work...
        return 'Email queued';
    });

// Or await the result later
$program = $sendEmail
    ->start()
    ->flatMap(function($handle) {
        // Do other work here...
        $otherWork = io(fn() => 'other work done');
        
        return $otherWork->map(function($result) use ($handle) {
            // Now wait for email to finish
            $emailResult = $handle->await();
            return [$result, $emailResult];
        });
    });

// Custom execution context
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

$heavyComputation = io(fn() => processLargeDataset());
$handle = $heavyComputation
    ->start(new ParallelExecutionContext())  // Use parallel threads
    ->unsafeRun();
```

## Features

- Pure functional effect handling
- Type-safe IO operations
- Composable effect chains
- Error handling through Either
- Resource management
- **Async execution with `start()`** - Fork computations to background fibers
- **Custom execution contexts** - Control how effects are executed

## Why Phunkie Effects?

- **Type Safety**: Catch errors at compile time
- **Referential Transparency**: Same input always produces the same output
- **Testability**: Easier to test pure functions
- **Composability**: Build complex programs from simple, pure functions
- **Resource Management**: Safe handling of resources like files and network connections

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Inspired by [cats-effect](https://typelevel.org/cats-effect/)
- Built on top of [Phunkie](https://github.com/phunkie/phunkie) 
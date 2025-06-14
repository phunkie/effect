# Phunkie Effect

A functional effects library for PHP inspired by Scala's cats-effect. Phunkie Effect provides a way to manage side effects in a purely functional way, making your code more predictable, testable, and maintainable.

## Installation

```bash
composer require phunkie/effect
```

## Requirements

- PHP 8.1 or higher

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

### IOApp

`IOApp` provides a way to run your IO programs. It's the entry point for your effectful applications.

```php
use Phunkie\Effect\Functions\io\io;
use Phunkie\Effect\IO\IO;
use Phunkie\Effect\IO\IOApp;

class MyApp extends IOApp
{
    public function run(): IO
    {
        return io(function() {
            echo "Hello, Effects!";
            return 0;
        });
    }
}
```

To run your application, use the Phunkie console:

```bash
$ bin/phunkie MyApp
Hello, Effects!
```

## Features

- Pure functional effect handling
- Type-safe IO operations
- Composable effect chains
- Error handling through Either
- Resource management
- Concurrency support (coming soon)

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
# Phunkie Effect

A functional effects library for PHP inspired by Scala's cats-effect. Phunkie Effects provides a way to manage side effects in a purely functional way, making your code more predictable, testable, and maintainable.

## Overview

Phunkie Effect brings the power of purely functional programming to PHP, allowing you to describe side effects as values that can be composed, tested, and reasoned about before execution.

## Getting Started

New to Phunkie Effect? Start here:

- [Introduction](introduction.md) - Learn what Phunkie Effect is and when to use it
- [Getting Started](getting-started.md) - Installation and your first effect
- [IO App and Console](io-app.md) - Build command-line applications

## Core Concepts

Master the fundamental building blocks:

- [Combinators](combinators.md) - Core IO operations and composition patterns
- [Resources](resources.md) - Safe resource management with brackets
- [Concurrency](concurrency.md) - Parallel execution and concurrent programming

## Documentation Index

### 1. [Introduction](introduction.md)
   - What is Phunkie Effect?
   - Philosophy and design goals
   - When to use Phunkie Effect

### 2. [Getting Started](getting-started.md)
   - Installation
   - Basic Usage
   - Your First Effect

### 3. [IO App and IO Console](io-app.md)
   - Console Functions
   - Creating an IO App
   - Exit Codes and Error Handling
   - Best Practices

### 4. [Combinators](combinators.md)
   - Core IO Operations
   - Error Handling
   - Composition Patterns

### 5. [Concurrency](concurrency.md)
   - Blockers
   - Execution Context
   - Parallel Execution
   - Sequence and Traverse
   - Race, Both, Either
   - Cancellation
   - Delay
   - Channels

### 6. [Resources](resources.md)
   - Brackets
   - Resource Combinators
   - Files
   - URL
   - Database

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT Licence - see the LICENSE file for details.

## Acknowledgments

- Inspired by [cats-effect](https://typelevel.org/cats-effect/)
- Built on top of [Phunkie](https://github.com/phunkie/phunkie)

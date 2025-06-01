# Phunkie Effects

A functional effects library for PHP inspired by Scala's cats-effect. Phunkie Effects provides a way to manage side effects in a purely functional way, making your code more predictable, testable, and maintainable.

## Table of Contents

1. [Introduction](introduction.md)
   - What is Phunkie Effect?
   - Philosophy and design goals
   - When to use Phunkie Effect

2. [Getting Started](getting-started.md)
   - Installation
   - Basic Usage
   - Your First Effect

3. [IO App and IO Console](io-app.md)
   - Console Functions
   - Creating an IO App
   - Exit Codes and Error Handling
   - Best Practices

4. [Concurrency](concurrency.md)
   - Blockers
   - Execution Context
   - Parallel Execution
   - Sequence and Traverse
   - Race, Both, Either
   - Cancellation
   - Delay
   - Channels

5. [Resources](resources.md)
   - Brackets
   - Resource Combinators
   - Files
   - URL
   - Database

6. [Sockets](sockets.md)
    - Socket Types
    - Socket Options
    - Socket Lifecycle

7. [Networks](networks.md)
    - TCP
      - Server
      - Client
      - Connection Management
    - UDP
      - Datagram Handling
      - Broadcasting
      - Multicasting

8. [Cookbook](cookbook.md)
    - Supervisor Patterns
      - One-for-One Strategy
      - All-for-One Strategy
      - Custom Supervision
    - Shared State with Ref
      - Atomic Updates
      - Cross-Fiber Communication
    - Graceful Shutdown
      - Resource Cleanup
      - Signal Handling
    - Circuit Breakers
      - Failure Detection
      - Automatic Recovery
    - Rate Limiting
      - Token Bucket
      - Leaky Bucket

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Inspired by [cats-effect](https://typelevel.org/cats-effect/)
- Built on top of [Phunkie](https://github.com/phunkie/phunkie)

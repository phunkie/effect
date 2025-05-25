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
   - Creating an IO App
   - Running with IO Console
   - Exit Codes and Error Handling

4. [Blocker](blocker.md)
   - Understanding Blocking Operations
   - Managing Blocking Contexts
   - Best Practices

5. [Combinators](combinators.md)
   - Map and FlatMap
   - Sequence and Traverse
   - Race and Timeout
   - Error Handling

6. [Concurrency](concurrency.md)
   - Parallel Execution
     - Sequence
     - Traverse
     - ParMap
   - Concurrent Operations
     - Race
     - Both
     - Either
   - Resource Management
     - Bracket
     - Resource
   - Cancellation
     - Cancelable
     - Uncancelable

7. [Bracket](bracket.md)
   - Understanding Bracket
   - Acquire-Use-Release Pattern
   - Error Handling in Bracket
   - Nested Brackets
   - Best Practices

8. [Resources](resources.md)
   - Files
     - Reading and Writing
     - Streams and Buffers
   - URLs
     - HTTP Clients
     - WebSockets
   - Database Connections
     - Connection Pools
     - Transactions
     - Query Execution

9. [Fibers](fibers.md)
   - Creating Fibers
   - Fiber Scheduling
   - Fiber Communication
   - Error Propagation

10. [Sockets](sockets.md)
    - Socket Types
    - Socket Options
    - Socket Lifecycle

11. [Networks](networks.md)
    - TCP
      - Server
      - Client
      - Connection Management
    - UDP
      - Datagram Handling
      - Broadcasting
      - Multicasting

12. [Cookbook](cookbook.md)
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

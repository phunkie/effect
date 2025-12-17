# IO App and IO Console

IOApp is the entry point for your effectful applications. It provides a structured way to run your IO programs and handle their lifecycle.

## Exit Codes

Phunkie Effects provides constants for common exit codes:

```php
use const Phunkie\Effect\IOApp\ExitSuccess;     // 0
use const Phunkie\Effect\IOApp\ExitFailure;     // 1
use const Phunkie\Effect\IOApp\ExitMisuse;      // 2
use const Phunkie\Effect\IOApp\ExitCannotExec;  // 126
use const Phunkie\Effect\IOApp\ExitNotFound;    // 127
use const Phunkie\Effect\IOApp\ExitInvalid;     // 128
use const Phunkie\Effect\IOApp\ExitInterrupted; // 130
```

## Console Functions

Phunkie Effects provides a set of console functions that return IO values for safe console interaction:

```php
use function Phunkie\Effect\Functions\console\printLn;
use function Phunkie\Effect\Functions\console\readLine;
use function Phunkie\Effect\Functions\console\printError;
use function Phunkie\Effect\Functions\console\printWarning;
use function Phunkie\Effect\Functions\console\printSuccess;
use function Phunkie\Effect\Functions\console\printInfo;
use function Phunkie\Effect\Functions\console\printDebug;
use function Phunkie\Effect\Functions\console\printTable;
use function Phunkie\Effect\Functions\console\printProgress;
use function Phunkie\Effect\Functions\console\printSpinner;

// Basic output
printLn("Hello, World!")->unsafeRun();

// Reading input
$name = readLine("Enter your name: ")->unsafeRun();

// Colored output
printError("Something went wrong")->unsafeRun();
printWarning("Be careful")->unsafeRun();
printSuccess("Operation completed")->unsafeRun();
printInfo("Just FYI")->unsafeRun();
printDebug("Variable value: 42")->unsafeRun();

// Tables
$data = [
    ['Name', 'Age', 'City'],
    ['John', '30', 'New York'],
    ['Jane', '25', 'London']
];
printTable($data)->unsafeRun();

// Progress indicators
printProgress(50, 100)->unsafeRun();
printSpinner("Processing...")->unsafeRun();
```

All console functions return IO values, ensuring that side effects are properly managed and composed.

## Creating an IO App

To create an IO application, extend the `IOApp` class and implement the `run` method:

```php
use Phunkie\Effect\IO\IOApp;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\console\printLn;
use const Phunkie\Effect\IOApp\ExitSuccess;

class MyApp extends IOApp
{
    public function __construct()
    {
        parent::__construct("1.0.0"); // Optional version string
    }

    /**
     * @return IO<int>
     */
    public function run(?array $args = []): IO
    {
        return printLn("Hello, Effects!")
            ->map(fn() => ExitSuccess);
    }
}
```

The `run` method must return an `IO` that will be executed when the application starts. The return value of the IO will be used as the application's exit code.

### Version Support

IOApp automatically provides `--version` and `-v` flags. You can specify your application version in the constructor:

```php
class MyApp extends IOApp
{
    public function __construct()
    {
        parent::__construct("2.1.0");
    }
    
    // ... rest of implementation
}
```

Running with the version flag:
```bash
$ bin/phunkie MyApp.php --version
2.1.0
```

## Command-Line Argument Parsing

IOApp provides a powerful DSL for defining and parsing command-line arguments:

```php
use Phunkie\Effect\IO\IOApp;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\ioapp\arguments;
use function Phunkie\Effect\Functions\ioapp\option;
use const Phunkie\Effect\Functions\ioapp\Required;
use const Phunkie\Effect\Functions\ioapp\Optional;
use const Phunkie\Effect\Functions\ioapp\NoInput;
use const Phunkie\Effect\Functions\ioapp\Negatable;

class MyApp extends IOApp
{
    protected function define(): Validation
    {
        return arguments(
            option('f', 'file', 'Input file path', Required),
            option('o', 'output', 'Output file path', Optional),
            option('v', 'verbose', 'Enable verbose output', NoInput),
            option('c', 'color', 'Enable colored output', Negatable)
        );
    }

    public function run(?array $args = []): IO
    {
        return $this->parse($args)->fold(
            fn($errors) => $this->showUsage($errors)
        )(
            fn($options) => $this->processOptions($options)
        );
    }

    private function processOptions($options): IO
    {
        return new IO(function() use ($options) {
            // Access parsed options
            $file = $options->fetch('file')->getOrElse('default.txt');
            $verbose = $options->has('verbose');
            
            // Access positional arguments
            $positionalArgs = $options->args;
            
            // Your application logic here
            return 0;
        });
    }
}
```

### Option Formats

- **Required**: Option must have a value (`-f file.txt` or `--file=file.txt`)
- **Optional**: Option may have a value, defaults to `true` if present without value
- **NoInput**: Flag option, no value expected (`-v` or `--verbose`)
- **Negatable**: Can be negated with `no-` prefix (`--color` or `--no-color`)

### Accessing Parsed Options

```php
// Check if option exists
if ($options->has('verbose')) {
    // ...
}

// Fetch option value (returns Either<Error, Input>)
$options->fetch('file')->fold(
    fn($error) => printLn("File not specified"),
    fn($input) => printLn("File: " . $input->value)
);

// Get with default
$file = $options->fetch('file')->getOrElse('default.txt');

// Access positional arguments
$files = $options->args; // Array of non-option arguments
```

### Built-in Options

IOApp automatically provides:
- `-h, --help`: Display usage information
- `-v, --version`: Display application version

## Running with IO Console

Phunkie Effects provides a console application to run your IO apps in multiple ways:

### Running IOApp Classes

You can run an IOApp by passing a file that defines the class:

```bash
$ bin/phunkie MyApp.php
Hello, Effects!
```

Simply define your IOApp class in the file - no need to explicitly return an instance:

```php
<?php
// MyApp.php
require 'vendor/autoload.php';

use Phunkie\Effect\IO\IOApp;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\console\printLn;

class MyApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return printLn("Hello, Effects!")
            ->map(fn() => 0);
    }
}
```

### Explicit Instantiation (Optional)

If you prefer, you can explicitly return an instance of your IOApp:

```php
<?php
// MyApp.php
require 'vendor/autoload.php';

use Phunkie\Effect\IO\IOApp;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\console\printLn;

class MyApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return printLn("Hello, Effects!")
            ->map(fn() => 0);
    }
}

return new MyApp(); // Explicit return is optional but supported
```

### Running Plain IO

You can also run a file that returns a plain `IO` value:

```php
<?php
// hello.php
require 'vendor/autoload.php';

use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\console\printLn;

return printLn("Hello from IO!")
    ->map(fn() => 0);
```

```bash
$ bin/phunkie hello.php
Hello from IO!
```

### Passing Arguments

Arguments are passed to the IOApp's `run` method:

```php
class MyApp extends IOApp
{
    public function run(?array $args = []): IO
    {
        return new IO(function() use ($args) {
            echo "Arguments: " . implode(", ", $args) . "\n";
            return 0;
        });
    }
}
```

```bash
$ bin/phunkie MyApp.php arg1 arg2 arg3
Arguments: MyApp.php, arg1, arg2, arg3
```

The console will:
1. Load your application class or IO
2. Execute the `run` method (for IOApp) or the IO directly
3. Handle any errors that occur during execution
4. Return the appropriate exit code

## Exit Codes and Error Handling

IOApp provides a way to handle errors and return appropriate exit codes:

```php
use Phunkie\Effect\IO\IOApp;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\console\printError;
use const Phunkie\Effect\IOApp\ExitSuccess;
use const Phunkie\Effect\IOApp\ExitFailure;

class MyApp extends IOApp
{
    /**
     * @return IO<int>
     */
    public function run(): IO
    {
        return io(function() {
            try {
                // Your application logic here
                return ExitSuccess;
            } catch (\Exception $e) {
                return printError($e->getMessage())
                    ->map(fn() => ExitFailure);
            }
        });
    }
}
```

## Best Practices

1. **Keep it Pure**: The `run` method should return an IO without side effects. All side effects should be wrapped in IO.

2. **Error Handling**: Use proper error handling and return meaningful exit codes.

3. **Resource Management**: Use bracket or resource patterns to manage resources properly.

4. **Composition**: Break down your application into smaller, composable IOs.

Example of a well-structured IOApp:

```php
use Phunkie\Effect\IO\IOApp;
use function Phunkie\Effect\Functions\io\io;
use function Phunkie\Effect\Functions\console\printLn;
use function Phunkie\Effect\Functions\console\printError;
use function Phunkie\Effect\Functions\console\printSuccess;
use const Phunkie\Effect\IOApp\ExitSuccess;
use const Phunkie\Effect\IOApp\ExitFailure;

class MyApp extends IOApp
{
    /**
     * @return IO<int>
     */
    public function run(): IO
    {
        return io(function() {
            try {
                $config = $this->loadConfig();
                $db = $this->connectToDatabase($config);
                
                return $this->runApplication($db)
                    ->flatMap(function($result) use ($db) {
                        return printSuccess("Operation completed")
                            ->map(function() use ($db) {
                                $this->cleanup($db);
                                return ExitSuccess;
                            });
                    })
                    ->handleError(function($error) {
                        return printError($error->getMessage())
                            ->map(fn() => ExitFailure);
                    })
                    ->unsafeRun();
            } catch (\Exception $e) {
                return printError("Fatal error: " . $e->getMessage())
                    ->map(fn() => ExitFailure);
            }
        });
    }

    private function loadConfig(): IO
    {
        return io(function() {
            // Load configuration
            return ['host' => 'localhost', 'port' => 5432];
        });
    }

    private function connectToDatabase(array $config): IO
    {
        return io(function() use ($config) {
            // Connect to database
            return new Database($config);
        });
    }

    private function runApplication(Database $db): IO
    {
        return io(function() use ($db) {
            // Run application logic
            return $db->query("SELECT * FROM users");
        });
    }

    private function cleanup(Database $db): void
    {
        $db->close();
    }
}
```

This example shows:
- Proper error handling with console functions
- Resource management
- Composition of IOs
- Clean separation of concerns
- Meaningful exit codes 
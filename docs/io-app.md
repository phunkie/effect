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

## Creating an IO App

To create an IO application, extend the `IOApp` class and implement the `run` method:

```php
use Phunkie\Effect\IO\IOApp;
use function Phunkie\Effect\Functions\io\io;
use const Phunkie\Effect\IOApp\ExitSuccess;

class MyApp extends IOApp
{
    /**
     * @return IO<int>
     */
    public function run(): IO
    {
        return io(function() {
            echo "Hello, Effects!";
            return ExitSuccess;
        });
    }
}
```

The `run` method must return an `IO` that will be executed when the application starts. The return value of the IO will be used as the application's exit code.

## Running with IO Console

Phunkie Effects provides a console application to run your IO apps. After installing the Phunkie console, you can run your application using:

```bash
$ bin/phunkie MyApp
Hello, Effects!
```

The console will:
1. Load your application class
2. Execute the `run` method
3. Handle any errors that occur during execution
4. Return the appropriate exit code

## Exit Codes and Error Handling

IOApp provides a way to handle errors and return appropriate exit codes:

```php
use Phunkie\Effect\IO\IOApp;
use function Phunkie\Effect\Functions\io\io;
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
                echo "Error: " . $e->getMessage() . "\n";
                return ExitFailure;
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
                    ->map(function($result) {
                        $this->cleanup($db);
                        return ExitSuccess;
                    })
                    ->handleError(function($error) {
                        echo "Error: " . $error->getMessage() . "\n";
                        return ExitFailure;
                    })
                    ->unsafeRun();
            } catch (\Exception $e) {
                echo "Fatal error: " . $e->getMessage() . "\n";
                return ExitFailure;
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
- Proper error handling
- Resource management
- Composition of IOs
- Clean separation of concerns
- Meaningful exit codes 
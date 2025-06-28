<?php

namespace Tests\Phunkie\Effect\Functions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Phunkie\Effect\IO\IO;
use function Phunkie\Effect\Functions\console\printLines;
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

class ConsoleTest extends TestCase
{
    private $originalStdin;

    protected function setUp(): void
    {
        $this->originalStdin = STDIN;
    }

    protected function tearDown(): void
    {
        if (is_resource($this->originalStdin)) {
            fclose($this->originalStdin);
        }
    }

    #[Test]
    public function it_prints_a_line()
    {
        $this->expectOutputString("Hello, World!\n");
        printLn("Hello, World!")->unsafeRun();
    }

    #[Test]
    public function it_prints_many_lines()
    {
        $this->expectOutputString("Line 1\nLine 2\nLine 3\n");
        printLines(ImmList('Line 1', 'Line 2', 'Line 3'))->unsafeRun();
    }

    #[Test]
    public function it_reads_a_line()
    {
        $input = "test input\n";
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);
        
        $readLine = function(string $prompt) use ($stream): IO {
            return new IO(function() use ($prompt, $stream) {
                print($prompt);
                $input = fgets($stream);
                return $input === false ? "" : trim($input);
            });
        };
        
        $this->expectOutputString("Enter something: ");
        $result = $readLine("Enter something: ")->unsafeRun();
        
        $this->assertEquals("test input", $result);
        fclose($stream);
    }

    #[Test]
    public function it_prints_error_messages()
    {
        $this->expectOutputString("\033[31mError: Something went wrong\033[0m\n");
        printError("Something went wrong")->unsafeRun();
    }

    #[Test]
    public function it_prints_warning_messages()
    {
        $this->expectOutputString("\033[33mWarning: Be careful\033[0m\n");
        printWarning("Be careful")->unsafeRun();
    }

    #[Test]
    public function it_prints_success_messages()
    {
        $this->expectOutputString("\033[32mSuccess: Operation completed\033[0m\n");
        printSuccess("Operation completed")->unsafeRun();
    }

    #[Test]
    public function it_prints_info_messages()
    {
        $this->expectOutputString("\033[36mInfo: Just FYI\033[0m\n");
        printInfo("Just FYI")->unsafeRun();
    }

    #[Test]
    public function it_prints_debug_messages()
    {
        $this->expectOutputString("\033[35mDebug: Variable value: 42\033[0m\n");
        printDebug("Variable value: 42")->unsafeRun();
    }

    #[Test]
    public function it_prints_a_table()
    {
        $data = [
            ['Name', 'Age', 'City'],
            ['John', '30', 'New York'],
            ['Jane', '25', 'London']
        ];
        
        $expected = "+------+-----+----------+\n" .
                   "| Name | Age | City     |\n" .
                   "+------+-----+----------+\n" .
                   "| John | 30  | New York |\n" .
                   "| Jane | 25  | London   |\n" .
                   "+------+-----+----------+\n";
        
        $this->expectOutputString($expected);
        printTable($data)->unsafeRun();
    }

    #[Test]
    public function it_prints_progress()
    {
        $this->expectOutputString("\rProgress: [==========>          ] 50%");
        printProgress(50, 100)->unsafeRun();
    }

    #[Test]
    public function it_prints_a_spinner()
    {
        $this->expectOutputString("\rProcessing... |");
        printSpinner("Processing...")->unsafeRun();
    }
} 

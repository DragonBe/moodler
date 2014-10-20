<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 20/10/14
 * Time: 11:34
 */

namespace MoodlerTest;


use Moodler\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    protected $logfile;
    protected function setUp()
    {
        parent::setUp();
        $this->logfile = __DIR__ . '/_files/application.log';
    }
    protected function tearDown()
    {
        if (file_exists($this->logfile)) {
            unlink($this->logfile);
        }
        $this->logfile = null;
        parent::tearDown();
    }

    public function testLoggerCanReportMessages()
    {
        $message = 'Test message';
        $logger = new Logger();
        $logger->setLogFile($this->logfile);
        $logger->setLogLevel(Logger::LOG_LEVEL_DEBUG);
        $logger->log($message);

        $contents = file_get_contents($this->logfile);

        $expected = sprintf(
            '[%s]: %s',
            $logger->getLogLevel(),
            $message
        ) . PHP_EOL;

        $this->assertStringEndsWith($expected, $contents);
    }

    public function testConfigureLoggerWithoutLogFileProvisioned()
    {
        $logger = new Logger();
        $logger->log('Testing');

        $this->assertFileNotExists($this->logfile);
    }

    public function testLoggerDoesNotLogMessagesBelowTreshold()
    {
        $message = 'Test message';
        $logger = new Logger();
        $logger->setLogFile($this->logfile);
        $logger->setLogLevel(Logger::LOG_LEVEL_INFO);
        $logger->log($message, Logger::LOG_LEVEL_WARN);
        $logger->log($message, Logger::LOG_LEVEL_DEBUG);
        $logger->log($message, Logger::LOG_LEVEL_INFO);

        $contents = file_get_contents($this->logfile);
        $lines = explode(PHP_EOL, $contents);
        array_pop($lines); // Removing trailing PHP_EOL

        $expected = array (
            0 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_WARN,
                $message
            ),
            1 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_INFO,
                $message
            ),
        );
        $this->assertSame(2, count($lines));
        foreach ($expected as $index => $testLine) {
            $this->assertStringEndsWith($testLine, $lines[$index]);
        }
    }

    public function testLoggerDoesLogMessagesEqualToTreshold()
    {
        $message = 'Test message';
        $logger = new Logger();
        $logger->setLogFile($this->logfile);
        $logger->setLogLevel(Logger::LOG_LEVEL_INFO);
        $logger->log($message, Logger::LOG_LEVEL_WARN);
        $logger->log($message, Logger::LOG_LEVEL_INFO);
        $logger->log($message, Logger::LOG_LEVEL_CRIT);

        $contents = file_get_contents($this->logfile);
        $lines = explode(PHP_EOL, $contents);
        array_pop($lines);

        $expected = array (
            0 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_WARN,
                $message
            ),
            1 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_INFO,
                $message
            ),
            2 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_CRIT,
                $message
            ),
        );

        $this->assertSame(3, count($lines));
        foreach ($expected as $index => $testLine) {
            $this->assertStringEndsWith($testLine, $lines[$index]);
        }
    }
    public function testCanCallAliasesOnLogLevels()
    {
        $message = 'Test message';
        $logger = new Logger();
        $logger->setLogFile($this->logfile);
        $logger->setLogLevel(Logger::LOG_LEVEL_INFO);
        $logger->warn($message);
        $logger->info($message);
        $logger->crit($message);

        $contents = file_get_contents($this->logfile);
        $lines = explode(PHP_EOL, $contents);
        array_pop($lines);

        $expected = array (
            0 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_WARN,
                $message
            ),
            1 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_INFO,
                $message
            ),
            2 => sprintf(
                '[%s]: %s',
                Logger::LOG_LEVEL_CRIT,
                $message
            ),
        );

        $this->assertSame(3, count($lines));
        foreach ($expected as $index => $testLine) {
            $this->assertStringEndsWith($testLine, $lines[$index]);
        }
    }
} 
<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 20/10/14
 * Time: 11:26
 */

namespace Moodler;


class Logger
{
    const LOG_LEVEL_HALT   = 'halt';
    const LOG_LEVEL_CRIT   = 'crit';
    const LOG_LEVEL_WARN   = 'warn';
    const LOG_LEVEL_NOTICE = 'notice';
    const LOG_LEVEL_INFO   = 'info';
    const LOG_LEVEL_DEBUG  = 'debug';

    /**
     * @var string $logFile The file where messages are logged into
     */
    protected $logFile;
    /**
     * @var string The minimum log level that should be used
     */
    protected $logLevel;

    /**
     * @param null|string $logFile The logfile for tracking activity
     * @param string $logLevel The level of logging verbosity
     */
    public function __construct($logFile = null, $logLevel = self::LOG_LEVEL_DEBUG)
    {
        $this->setLogFile($logFile);
        $this->setLogLevel($logLevel);
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param string $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    public function log($message, $logLevel = self::LOG_LEVEL_DEBUG)
    {
        if (null === $this->getLogFile()) {
            return false;
        }
        if (false === ($this->isAllowed($logLevel))) {
            return false;
        }
        $entry = sprintf(
            '%s %s [%s]: %s',
            date('r'),
            $this->getIpAddress(),
            $logLevel,
            $message
        ) . PHP_EOL;
        $fileHandler = fopen($this->getLogFile(), 'a');
        fwrite($fileHandler, $entry);
        fclose($fileHandler);
        return true;
    }

    protected function isAllowed($logLevel)
    {
        $logLevels = array (
            self::LOG_LEVEL_HALT   => 0,
            self::LOG_LEVEL_CRIT   => 1,
            self::LOG_LEVEL_WARN   => 2,
            self::LOG_LEVEL_NOTICE => 3,
            self::LOG_LEVEL_INFO   => 4,
            self::LOG_LEVEL_DEBUG  => 5,
        );
        if (!array_key_exists($logLevel, $logLevels)) {
            return false;
        }
        return ($logLevels[$logLevel] <= $logLevels[$this->logLevel]);
    }

    private function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '-';
        }
        return $ipAddress;
    }

    public function __call($name, $arguments)
    {
        $this->log(implode(', ', $arguments), $name);
    }
}
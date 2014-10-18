<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 17/10/14
 * Time: 22:37
 */

namespace MoodlerTest;

use \Moodler\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage foo is not a file
     * @covers \Moodler\Config::__construct
     * @covers \Moodler\Config::load
     */
    public function testConfigRejectsNonExistingFile()
    {
        $config = new Config();
        $config->load('foo');
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /.* is not readable/
     * @covers \Moodler\Config::__construct
     * @covers \Moodler\Config::load
     */
    public function testConfigRejectsNonReadableConfigFile()
    {
        $file = __DIR__ . '/_files/foo';
        touch($file);
        chmod($file, 0000);
        $config = new Config();
        $config->load($file);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Cannot parse/
     * @covers \Moodler\Config::__construct
     * @covers \Moodler\Config::load
     */
    public function testConfigRejectsInvalidConfigFile()
    {
        $file = __DIR__ . '/_files/config.foo';
        $config = new Config();
        $config->load($file);
    }

    public function testCanLoadConfiguration()
    {
        $config = new Config();
        $config->load(__DIR__ . '/_files/config.ini');
        $this->assertSame('mysqli:host=127.0.0.1;dbname=moodler', $config->getDsn());
        $this->assertSame('moodler', $config->getUsername());
        $this->assertSame('moodler', $config->getPassword());
    }

    public function testCanLoadConfigurationAtConstruct()
    {
        $config = new Config(__DIR__ . '/_files/config.ini');
        $this->assertSame('mysqli:host=127.0.0.1;dbname=moodler', $config->getDsn());
        $this->assertSame('moodler', $config->getUsername());
        $this->assertSame('moodler', $config->getPassword());
    }
} 
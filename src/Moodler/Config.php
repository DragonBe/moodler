<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 17/10/14
 * Time: 22:17
 */

namespace Moodler;


class Config
{
    protected $config;
    /**
     * @var string The DSN for the database configuration
     */
    protected $dsn;
    /**
     * @var string The DB username
     */
    protected $username;
    /**
     * @var string The DB password
     */
    protected $password;

    /**
     * @param string $configFile
     */
    public function __construct($configFile = null)
    {
        if (null !== $configFile) {
            $this->load($configFile);
        }
    }

    /**
     * @param string $configFile
     */
    public function load($configFile)
    {
        if (!is_file($configFile)) {
            throw new \InvalidArgumentException(
                sprintf('%s is not a file', (string) $configFile)
            );
        }
        if (!is_readable($configFile)) {
            throw new \RuntimeException(
                sprintf('%s is not readable', (string) $configFile)
            );
        }
        $config = parse_ini_file($configFile);
        if (false === $config || array () === $config) {
            throw new \RuntimeException(
                sprintf('Cannot parse %s', $configFile)
            );
        }

        $this->config = $config;

        $params = array (
            'db.dsn'      => 'setDsn',
            'db.username' => 'setUsername',
            'db.password' => 'setPassword',
        );

        foreach ($params as $key => $method) {
            if (isset ($config[$key])) {
                $this->$method($config[$key]);
            }
        }
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

} 
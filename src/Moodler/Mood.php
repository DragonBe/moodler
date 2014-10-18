<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 17/10/14
 * Time: 09:32
 */

namespace Moodler;


class Mood
{
    const MOOD_SUPER = 'superhappy';
    const MOOD_HAPPY = 'happy';
    const MOOD_NORM  = 'normal';
    const MOOD_SAD   = 'sad';
    const MOOD_CRY   = 'crying';
    const DEFAULT_ORG = 'demo';

    /**
     * @var Config $config The configuration for the application
     */
    protected $config;

    /**
     * @var \PDO $pdo The connection to the database
     */
    protected $pdo;

    /**
     * @var string $organisation The default organisation for this mood
     */
    protected $organisation;

    public function __construct($config = null, $organisation = self::DEFAULT_ORG)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }
        $this->setOrganisation($organisation);
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        if (null === $this->pdo) {
            $this->setPdo(new \PDO(
                $this->getConfig()->getDsn(),
                $this->getConfig()->getUsername(),
                $this->getConfig()->getPassword()
            ));
        }
        return $this->pdo;
    }

    /**
     * @param \PDO $pdo
     */
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param string $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function storeMood($mood)
    {
        $org = $this->getOrganisation();
        $allowedMoods = array (
            self::MOOD_CRY,
            self::MOOD_SAD,
            self::MOOD_NORM,
            self::MOOD_HAPPY,
            self::MOOD_SUPER,
        );
        if (!in_array($mood, $allowedMoods)) {
            throw new \InvalidArgumentException(
                sprintf('%s is not a valid mood', $mood)
            );
        }
        $sql = 'UPDATE `mood` SET `count` = `count` + 1 WHERE `mood` LIKE ? AND `org` LIKE ?';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindParam(1, $mood);
        $stmt->bindParam(2, $org);
        $stmt->execute();
    }
    public function getMoods()
    {
        $org = $this->getOrganisation();
        $sql = 'SELECT `mood`,`count`, (SELECT SUM(`count`) FROM `mood` WHERE `org` LIKE ?) AS `total` FROM `mood` WHERE `org` LIKE ?';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindParam(1, $org);
        $stmt->bindParam(2, $org);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
} 
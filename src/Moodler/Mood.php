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
     * @var Logger $logger A logging tool that allows us to track what's going on
     */
    protected $logger;

    /**
     * @var string $organisation The default organisation for this mood
     */
    protected $organisation;

    public function __construct($config = null, $organisation = self::DEFAULT_ORG)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }
        $this->setLogger(new Logger());
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
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return \Moodler\Mood
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
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

    /**
     * @param string $mood The mood to be stored
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function storeMood($mood)
    {
        $org = $this->getOrganisation();
        $this->getLogger()->info(sprintf('Storing mood "%s" for "%s"', $mood, $org));
        $allowedMoods = $this->getAvailableMoods();
        if (!in_array($mood, $allowedMoods)) {
            $this->getLogger()->crit(sprintf('Unaccepted mood %s', $mood));
            throw new \InvalidArgumentException(
                sprintf('%s is not a valid mood', $mood)
            );
        }
        $moodId = $this->getMoodId($org);
        $this->getLogger()->debug(sprintf('Found %d for %s', $moodId, $org));

        $sql = 'INSERT INTO `mood_count` (`moodId`, `mood`, `date`, `count`, `created`, `modified`) VALUES (?, ?, DATE(NOW()), 1, NOW(), NOW())';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindParam(1, $moodId);
        $stmt->bindParam(2, $mood);
        $success = $stmt->execute();
        $this->getLogger()->debug(sprintf('Executed "%s" with result [%s]', $stmt->queryString, $success ? 'true' : 'false'));

        if(false === $success) {
            $updateSql = 'UPDATE `mood_count` SET `count` = `count` + 1, `modified` = NOW() WHERE `moodId` = ? AND `mood` LIKE ? AND `date` LIKE DATE(NOW())';
            $update = $this->getPdo()->prepare($updateSql);
            $update->bindParam(1, $moodId);
            $update->bindParam(2, $mood);
            $updateSuccess = $update->execute();
            $this->getLogger()->debug(sprintf('Executed "%s" with result [%s]', $update->queryString, $updateSuccess ? 'true' : 'false'));
            if (false === $updateSuccess) {
                $this->getLogger->crit(sprintf('DB Failure: %s', var_export($update->errorInfo(),1)));
                throw new \RuntimeException('Cannot store the mood at this point');
            }
        }
    }
    public function getMoods()
    {
        $org = $this->getOrganisation();
        $this->getLogger()->debug(sprintf('Found organisation %s', $org));
        $sql = 'SELECT *, (SELECT SUM(`count`) FROM `mood_count` WHERE `date` LIKE DATE(NOW())) AS `total` FROM `mood` LEFT JOIN `mood_count` USING(`moodId`) WHERE `org` LIKE ? AND `date` LIKE DATE(NOW())';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindParam(1, $org);
        $stmt->execute();
        $this->getLogger()->debug(sprintf('Executed "%s"', $stmt->queryString));
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $moodList = array ();
        foreach ($this->getAvailableMoods() as $mood) {
            $item = new Count(array (
                'mood' => $mood,
                'count' => 0,
                'total' => 0
            ));
            foreach ($result as $entry) {
                if ($mood === $entry['mood']) {
                    $item = new Count($entry);
                }
            }
            $moodList[] = $item;
        }
        return array_reverse($moodList);
    }
    protected function getMoodId($org)
    {
        $sql = 'SELECT `moodId` FROM `mood` WHERE `org` LIKE ?';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindParam(1, $org);
        $stmt->execute();
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    protected function getAvailableMoods()
    {
        return array (
            self::MOOD_CRY,
            self::MOOD_SAD,
            self::MOOD_NORM,
            self::MOOD_HAPPY,
            self::MOOD_SUPER,
        );
    }
} 
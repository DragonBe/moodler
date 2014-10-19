<?php
/**
 * Created by PhpStorm.
 * User: dragonbe
 * Date: 19/10/14
 * Time: 05:42
 */

namespace Moodler;


class Count implements \ArrayAccess
{
    /**
     * @var string The mood
     */
    protected $mood;
    /**
     * @var int The count for this mood
     */
    protected $count;
    /**
     * @var int The total count for all moods
     */
    protected $total;

    function __construct($data = null)
    {
        $this->count = 0;
        $this->total = 0;
        if (null !== $data) {
            $this->populate($data);
        }
    }


    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return \Moodler\Count
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string
     */
    public function getMood()
    {
        return $this->mood;
    }

    /**
     * @param string $mood
     * @return \Moodler\Count
     */
    public function setMood($mood)
    {
        $this->mood = $mood;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return \Moodler\Count
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    public function populate($row)
    {
        if (is_array($row)) {
            $row = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
        }
        $this->setMood($row->mood)
            ->setCount($row->count)
            ->setTotal($row->total);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $method = 'get' . ucfirst($offset);
        return $this->$method();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $method = 'set' . ucfirst($offset);
        $this->$method($value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // No need to do anything here
    }

} 
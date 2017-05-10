<?php
namespace Fei\Service\Payment\Client\Utils\Builder;

use Fei\Service\Payment\Client\Utils\Builder\Fields\FieldInterface;

abstract class OperatorBuilder implements FieldInterface
{
    protected $builder;
    protected $value;
    protected $inCache;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Set the like operator for the current filter
     *
     * @param $value
     * @return $this
     */
    public function like($value)
    {
        $this->build("%$value%", 'LIKE');

        return $this;
    }

    /**
     * Set the like operator and begins with for the current filter
     *
     * @param $value
     * @return $this
     */
    public function beginsWith($value)
    {
        $this->build("$value%", 'LIKE');

        return $this;
    }

    /**
     * Set the like operator and ends with for the current filter
     *
     * @param $value
     * @return $this
     */
    public function endsWith($value)
    {
        $this->build("%$value", 'LIKE');

        return $this;
    }

    /**
     * Set the equal operator for the current filter
     *
     * @param $value
     * @return $this
     */
    public function equal($value)
    {
        $this->build("$value", '=');

        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function in(array $values)
    {
        $this->build($values, 'IN');

        return $this;
    }

    /**
     * Get InCache
     *
     * @return mixed
     */
    public function getInCache()
    {
        return $this->inCache;
    }

    /**
     * Set InCache
     *
     * @param mixed $inCache
     *
     * @return $this
     */
    public function setInCache($inCache)
    {
        $this->inCache = $inCache;
        return $this;
    }
}

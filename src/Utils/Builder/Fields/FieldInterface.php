<?php
namespace Fei\Service\Payment\Client\Utils\Builder\Fields;

interface FieldInterface
{
    /**
     * Build the filter
     *
     * @param $value
     * @param null $operator
     *
     * @return mixed
     */
    public function build($value, $operator = null);
}

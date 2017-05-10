<?php
namespace Fei\Service\Payment\Client\Utils\Builder\Fields;

use Fei\Service\Payment\Client\Utils\Builder\OperatorBuilder;

class Context extends OperatorBuilder
{
    public function build($value, $operator = null)
    {
        $search = $this->builder->getParams();
        $search['context_value'][] = $value;
        $search['context_operator'][] = $operator;
        $search['context_key'][] = $this->getInCache();

        $this->builder->setParams($search);
    }

    public function key($key)
    {
        $this->setInCache($key);
        return $this;
    }
}

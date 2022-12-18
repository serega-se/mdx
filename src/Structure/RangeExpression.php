<?php

namespace Se\Mdx\Structure;

class RangeExpression extends Expression
{
    private Expression $minExpressions;
    private Expression $maxExpressions;

    /**
     * @param Expression $minExpressions
     * @param Expression $maxExpressions
     */
    public function __construct(
        Expression $minExpressions,
        Expression $maxExpressions
    )
    {
        $this->minExpressions = $minExpressions;
        $this->maxExpressions = $maxExpressions;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf("{%s:%s}", $this->minExpressions, $this->maxExpressions);
    }

    /**
     * @return Expression
     */
    public function getMinExpressions(): Expression
    {
        return $this->minExpressions;
    }

    /**
     * @return Expression
     */
    public function getMaxExpressions(): Expression
    {
        return $this->maxExpressions;
    }
}
